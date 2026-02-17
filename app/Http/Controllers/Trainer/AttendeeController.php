<?php

namespace App\Http\Controllers\Trainer;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendeeController extends Controller
{

    /**
     * List attendees for a specific training.
     */
    public function index(Request $request, Training $training): View
    {
        $this->authorizeTrainerOwnership($request, $training);

        $attendees = $training->registrations()
            ->with('user')
            ->orderBy('created_at')
            ->paginate(25);

        return view('trainer.attendees.index', [
            'training' => $training,
            'attendees' => $attendees,
        ]);
    }

    /**
     * Mark a single attendee's registration as completed and trigger certificate generation.
     */
    public function markComplete(Request $request, Training $training, TrainingRegistration $registration): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $training);
        $this->ensureRegistrationBelongsToTraining($registration, $training);

        $registration->update([
            'status' => RegistrationStatus::Completed,
            'completed_at' => now(),
            'marked_complete_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('trainer.attendees.index', $training)
            ->with('success', "Completion recorded for {$registration->user->full_name}.");
    }

    /**
     * Mark multiple attendees as completed in bulk and trigger certificate generation for each.
     */
    public function bulkComplete(Request $request, Training $training): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $training);

        $validated = $request->validate([
            'registration_ids' => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['required', 'integer', 'exists:training_registrations,id'],
        ]);

        $registrations = TrainingRegistration::whereIn('id', $validated['registration_ids'])
            ->where('training_id', $training->id)
            ->where('status', '!=', RegistrationStatus::Completed)
            ->with('user')
            ->get();

        $completedCount = 0;

        foreach ($registrations as $registration) {
            $registration->update([
                'status' => RegistrationStatus::Completed,
                'completed_at' => now(),
                'marked_complete_by' => $request->user()->id,
            ]);

            $completedCount++;
        }

        return redirect()
            ->route('trainer.attendees.index', $training)
            ->with('success', "{$completedCount} attendee(s) marked as completed.");
    }

    /**
     * Export attendees for a training to CSV.
     */
    public function export(Request $request, Training $training): StreamedResponse
    {
        $this->authorizeTrainerOwnership($request, $training);

        $registrations = $training->registrations()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $filename = 'attendees_' . str_replace(' ', '_', $training->title) . '_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($registrations) {
            $handle = fopen('php://output', 'w');

            // CSV header row
            fputcsv($handle, [
                'Registration ID',
                'First Name',
                'Last Name',
                'Email',
                'Status',
                'Registered At',
                'Completed At',
                'Amount Paid',
            ]);

            foreach ($registrations as $registration) {
                fputcsv($handle, [
                    $registration->id,
                    $registration->user->first_name,
                    $registration->user->last_name,
                    $registration->user->email,
                    $registration->status->label(),
                    $registration->created_at->toDateTimeString(),
                    $registration->completed_at?->toDateTimeString() ?? '',
                    $registration->amount_paid_cents
                        ? '$' . number_format($registration->amount_paid_cents / 100, 2)
                        : '$0.00',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Verify that the authenticated user owns the given training.
     */
    protected function authorizeTrainerOwnership(Request $request, Training $training): void
    {
        if ($training->trainer_id !== $request->user()->id) {
            abort(403, 'You do not have permission to manage this training.');
        }
    }

    /**
     * Ensure that a registration belongs to the specified training.
     */
    protected function ensureRegistrationBelongsToTraining(TrainingRegistration $registration, Training $training): void
    {
        if ($registration->training_id !== $training->id) {
            abort(404, 'Registration not found for this training.');
        }
    }
}
