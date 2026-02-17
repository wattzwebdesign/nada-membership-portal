<?php

namespace App\Http\Controllers\Trainer;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\TrainingRegistration;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\TrainingCompletedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    use SafelyNotifies;

    public function index(Request $request): View
    {
        $trainer = $request->user();

        $query = TrainingRegistration::whereHas('training', function ($q) use ($trainer) {
            $q->where('trainer_id', $trainer->id);
        })->with(['user', 'training']);

        // Search by name or email
        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by training
        if ($trainingId = $request->input('training')) {
            $query->where('training_id', $trainingId);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $registrations = $query->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $trainings = $trainer->trainings()
            ->orderByDesc('start_date')
            ->get();

        return view('trainer.registrations.index', [
            'registrations' => $registrations,
            'trainings' => $trainings,
            'statuses' => RegistrationStatus::cases(),
        ]);
    }

    public function markComplete(Request $request, TrainingRegistration $registration): RedirectResponse
    {
        $trainer = $request->user();

        if ($registration->training->trainer_id !== $trainer->id) {
            abort(403, 'You do not have permission to manage this registration.');
        }

        $registration->update([
            'status' => RegistrationStatus::Completed,
            'completed_at' => now(),
            'marked_complete_by' => $trainer->id,
        ]);

        $this->safeNotify($registration->user, new TrainingCompletedNotification($registration));

        return redirect()
            ->back()
            ->with('success', "Completion recorded for {$registration->user->full_name}.");
    }

    public function bulkComplete(Request $request): RedirectResponse
    {
        $trainer = $request->user();

        $validated = $request->validate([
            'registration_ids' => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['required', 'integer', 'exists:training_registrations,id'],
        ]);

        $registrations = TrainingRegistration::whereIn('id', $validated['registration_ids'])
            ->whereHas('training', function ($q) use ($trainer) {
                $q->where('trainer_id', $trainer->id);
            })
            ->where('status', '!=', RegistrationStatus::Completed)
            ->with('user')
            ->get();

        $completedCount = 0;

        foreach ($registrations as $registration) {
            $registration->update([
                'status' => RegistrationStatus::Completed,
                'completed_at' => now(),
                'marked_complete_by' => $trainer->id,
            ]);

            $this->safeNotify($registration->user, new TrainingCompletedNotification($registration));

            $completedCount++;
        }

        return redirect()
            ->back()
            ->with('success', "{$completedCount} attendee(s) marked as completed.");
    }
}
