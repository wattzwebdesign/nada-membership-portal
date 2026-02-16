<?php

namespace App\Http\Controllers;

use App\Enums\DiscountType;
use App\Models\DiscountRequest;
use App\Models\SiteSetting;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\DiscountRequestedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiscountRequestController extends Controller
{
    use SafelyNotifies;
    /**
     * Show the discount request form.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // Check if the user already has an approved discount
        $existingApproved = $user->discountRequests()
            ->where('status', 'approved')
            ->first();

        // Check if there is already a pending request
        $pendingRequest = $user->discountRequests()
            ->where('status', 'pending')
            ->first();

        return view('discount.create', compact('user', 'existingApproved', 'pendingRequest'));
    }

    /**
     * Submit a new discount request.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Prevent duplicate pending requests
        $hasPending = $user->discountRequests()->where('status', 'pending')->exists();
        if ($hasPending) {
            return back()->with('error', 'You already have a pending discount request. Please wait for it to be reviewed.');
        }

        $rules = [
            'discount_type' => ['required', Rule::in([DiscountType::Student->value, DiscountType::Senior->value])],
            'proof_description' => ['nullable', 'string', 'max:2000'],
            'proof_documents' => ['required', 'array', 'min:1'],
            'proof_documents.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ];

        if ($request->input('discount_type') === DiscountType::Student->value) {
            $rules['school_name'] = ['required', 'string', 'max:255'];
            $rules['years_remaining'] = ['required', 'integer', 'min:1', 'max:10'];
        }

        if ($request->input('discount_type') === DiscountType::Senior->value) {
            $rules['date_of_birth'] = ['required', 'date'];
        }

        $validated = $request->validate($rules);

        $discountRequest = DiscountRequest::create([
            'user_id' => $user->id,
            'discount_type' => $validated['discount_type'],
            'status' => 'pending',
            'proof_description' => $validated['proof_description'] ?? null,
            'school_name' => $validated['school_name'] ?? null,
            'years_remaining' => $validated['years_remaining'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'approval_token' => bin2hex(random_bytes(32)),
            'token_expires_at' => now()->addDays(30),
        ]);

        // Handle file uploads via Spatie Media Library
        if ($request->hasFile('proof_documents')) {
            foreach ($request->file('proof_documents') as $file) {
                $discountRequest->addMedia($file)->toMediaCollection('proof_documents');
            }
        }

        $this->safeNotifyRoute(SiteSetting::adminEmail(), new DiscountRequestedNotification($discountRequest));

        return redirect()->route('discount.request.status')
            ->with('success', 'Your discount request has been submitted and is pending review.');
    }

    /**
     * Show the status of the user's discount request(s).
     */
    public function status(Request $request): View
    {
        $discountRequests = $request->user()
            ->discountRequests()
            ->orderByDesc('created_at')
            ->get();

        return view('discount.status', compact('discountRequests'));
    }
}
