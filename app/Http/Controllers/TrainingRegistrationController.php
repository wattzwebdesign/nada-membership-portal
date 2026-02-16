<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\Training;
use App\Models\TrainingRegistration;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\TrainingRegisteredNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingRegistrationController extends Controller
{
    use SafelyNotifies;
    /**
     * List all training registrations for the authenticated user.
     */
    public function index(Request $request): View
    {
        $registrations = $request->user()
            ->trainingRegistrations()
            ->with('training.trainer')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('trainings.my-registrations', compact('registrations'));
    }

    /**
     * Register the authenticated user for a training.
     */
    public function store(Request $request, Training $training): RedirectResponse
    {
        $user = $request->user();

        // Check if already registered
        $existing = TrainingRegistration::where('training_id', $training->id)
            ->where('user_id', $user->id)
            ->where('status', '!=', RegistrationStatus::Canceled->value)
            ->first();

        if ($existing) {
            return back()->with('error', 'You are already registered for this training.');
        }

        // Check if the training is full
        if ($training->isFull()) {
            return back()->with('error', 'This training is full. No more spots are available.');
        }

        // Check if the training is still open for registration
        if ($training->start_date->isPast()) {
            return back()->with('error', 'Registration for this training has closed.');
        }

        // TODO: If training is paid, redirect to Stripe Checkout for payment
        // For now, register directly for free trainings
        if ($training->is_paid) {
            // Paid training registration will be handled via Stripe Checkout
            // Placeholder: redirect back with notice
            return back()->with('info', 'Paid training registration coming soon. Please contact support.');
        }

        $registration = TrainingRegistration::create([
            'training_id' => $training->id,
            'user_id' => $user->id,
            'status' => RegistrationStatus::Registered->value,
            'amount_paid_cents' => 0,
        ]);

        $this->safeNotify($user, new TrainingRegisteredNotification($registration));

        return redirect()->route('trainings.my-registrations')
            ->with('success', 'You have been registered for "' . $training->title . '".');
    }

    /**
     * Cancel a training registration.
     */
    public function destroy(Request $request, Training $training): RedirectResponse
    {
        $registration = TrainingRegistration::where('training_id', $training->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Only allow cancellation if still in 'registered' status and training hasn't started
        if ($registration->status !== RegistrationStatus::Registered) {
            return back()->with('error', 'This registration can no longer be canceled.');
        }

        if ($training->start_date->isPast()) {
            return back()->with('error', 'Cannot cancel registration for a training that has already started.');
        }

        $registration->update([
            'status' => RegistrationStatus::Canceled->value,
        ]);

        return redirect()->route('trainings.my-registrations')
            ->with('success', 'Your registration has been canceled.');
    }
}
