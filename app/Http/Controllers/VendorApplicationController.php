<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\VendorApplication;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\VendorApplicationSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorApplicationController extends Controller
{
    use SafelyNotifies;

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('public.sell', compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->isVendor()) {
            return redirect()->route('vendor.dashboard')
                ->with('info', 'You are already an approved vendor.');
        }

        if ($user) {
            $hasPending = $user->vendorApplications()->where('status', 'pending')->exists();
            if ($hasPending) {
                return back()->with('error', 'You already have a pending vendor application.');
            }
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'what_they_sell' => ['required', 'string', 'max:2000'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $application = VendorApplication::create([
            'user_id' => $user?->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'business_name' => $validated['business_name'],
            'what_they_sell' => $validated['what_they_sell'],
            'website' => $validated['website'] ?? null,
            'status' => 'pending',
        ]);

        if ($user) {
            $user->vendor_application_status = 'pending';
            $user->save();
        }

        $this->safeNotifyRoute(SiteSetting::adminEmail(), new VendorApplicationSubmittedNotification($application));

        return redirect()->route('vendor-application.success');
    }

    public function success(): View
    {
        return view('public.sell-thank-you');
    }
}
