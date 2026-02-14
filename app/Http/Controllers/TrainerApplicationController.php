<?php

namespace App\Http\Controllers;

use App\Models\TrainerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainerApplicationController extends Controller
{
    /**
     * Show the trainer application form.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        return view('account.upgrade-to-trainer', compact('user'));
    }

    /**
     * Submit a trainer application.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Prevent submission if already a trainer
        if ($user->isTrainer()) {
            return redirect()->route('dashboard')
                ->with('info', 'You are already a Registered Trainer.');
        }

        // Prevent duplicate pending applications
        $hasPending = $user->trainerApplications()->where('status', 'pending')->exists();
        if ($hasPending) {
            return back()->with('error', 'You already have a pending trainer application.');
        }

        $validated = $request->validate([
            'credentials' => ['required', 'string', 'max:5000'],
            'experience_description' => ['required', 'string', 'max:5000'],
            'license_number' => ['nullable', 'string', 'max:255'],
        ]);

        TrainerApplication::create([
            'user_id' => $user->id,
            'credentials' => $validated['credentials'],
            'experience_description' => $validated['experience_description'],
            'license_number' => $validated['license_number'] ?? null,
            'status' => 'pending',
        ]);

        // Update user's trainer application status
        $user->update(['trainer_application_status' => 'pending']);

        // TODO: Send notification email to admins about the new application

        return redirect()->route('trainer-application.create')
            ->with('success', 'Your trainer application has been submitted and is pending review.');
    }
}
