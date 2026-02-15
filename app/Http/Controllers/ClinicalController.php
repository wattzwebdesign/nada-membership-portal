<?php

namespace App\Http\Controllers;

use App\Models\Clinical;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\ClinicalSubmittedNotification;
use App\Notifications\Concerns\SafelyNotifies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalController extends Controller
{
    use SafelyNotifies;
    /**
     * Show the clinical submission form.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // Get all trainers for the dropdown
        $trainers = User::role('registered_trainer')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('clinicals.create', compact('user', 'trainers'));
    }

    /**
     * Submit a new clinical record.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'estimated_training_date' => ['nullable', 'date'],
            'trainer_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
            'treatment_logs' => ['nullable', 'array'],
            'treatment_logs.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ]);

        $clinical = Clinical::create([
            'user_id' => $request->user()->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'estimated_training_date' => $validated['estimated_training_date'] ?? null,
            'trainer_id' => $validated['trainer_id'],
            'status' => 'submitted',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Handle file uploads via Spatie Media Library
        if ($request->hasFile('treatment_logs')) {
            foreach ($request->file('treatment_logs') as $file) {
                $clinical->addMedia($file)->toMediaCollection('treatment_logs');
            }
        }

        $this->safeNotifyRoute(SiteSetting::adminEmail(), new ClinicalSubmittedNotification($clinical));

        return redirect()->route('clinicals.index')
            ->with('success', 'Your clinical submission has been received and is under review.');
    }

    /**
     * Show the authenticated user's clinical submission history.
     */
    public function index(Request $request): View
    {
        $clinicals = $request->user()
            ->clinicals()
            ->with('trainer:id,first_name,last_name')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('clinicals.index', compact('clinicals'));
    }
}
