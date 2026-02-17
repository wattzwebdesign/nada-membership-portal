<?php

namespace App\Http\Controllers\Trainer;

use App\Enums\TrainingStatus;
use App\Enums\TrainingType;
use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Training;
use App\Models\TrainingInvitee;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\TrainingSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    use SafelyNotifies;

    /**
     * List all trainings belonging to the authenticated trainer.
     */
    public function index(Request $request): View
    {
        $trainings = $request->user()
            ->trainings()
            ->withCount(['registrations'])
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('trainer.trainings.index', [
            'trainings' => $trainings,
        ]);
    }

    /**
     * Show the form for creating a new training.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        if (!$user->canCreateTrainings()) {
            $missing = [];
            if (!$user->hasConnectedStripeAccount()) {
                $missing[] = 'connect your Stripe account';
            }
            if (!$user->hasActiveTrainerPlan()) {
                $missing[] = 'have an active Registered Trainer plan';
            }

            return view('trainer.trainings.create-blocked', [
                'missing' => $missing,
                'hasStripe' => $user->hasConnectedStripeAccount(),
                'hasPlan' => $user->hasActiveTrainerPlan(),
            ]);
        }

        return view('trainer.trainings.create', [
            'trainingTypes' => TrainingType::cases(),
        ]);
    }

    /**
     * Store a newly created training and submit for approval.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!$request->user()->canCreateTrainings()) {
            return redirect()->route('trainer.trainings.create')
                ->with('error', 'You must have a connected Stripe account and an active trainer plan to create trainings.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', 'string', 'in:' . implode(',', array_column(TrainingType::cases(), 'value'))],
            'location_name' => ['nullable', 'string', 'max:255'],
            'location_address' => ['nullable', 'string', 'max:500'],
            'virtual_link' => ['nullable', 'url', 'max:500'],
            'start_date' => ['required', 'date', 'after:now'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'timezone' => ['required', 'string', 'max:50'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'is_paid' => ['boolean'],
            'price' => ['required_if:is_paid,true', 'nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'is_group' => ['boolean'],
            'invitees' => ['array'],
            'invitees.*' => ['nullable', 'email', 'max:255'],
        ]);

        // Group trainings are always free
        $isGroup = !empty($validated['is_group']);
        if ($isGroup) {
            $validated['is_paid'] = false;
            $validated['price'] = null;
        }

        if (!empty($validated['price'])) {
            $validated['price_cents'] = (int) round($validated['price'] * 100);
        }
        unset($validated['price']);

        $validated['trainer_id'] = $request->user()->id;
        $validated['status'] = TrainingStatus::PendingApproval;
        $validated['is_group'] = $isGroup;

        // Remove invitees from validated before creating training
        $inviteeEmails = array_filter($validated['invitees'] ?? []);
        unset($validated['invitees']);

        $training = Training::create($validated);

        // Save invitees for group trainings
        if ($isGroup && !empty($inviteeEmails)) {
            foreach ($inviteeEmails as $email) {
                $training->invitees()->create(['email' => strtolower(trim($email))]);
            }
        }

        // Notify admin
        $this->safeNotifyRoute(SiteSetting::adminEmail(), new TrainingSubmittedNotification($training));

        return redirect()
            ->route('trainer.trainings.edit', $training)
            ->with('success', 'Training submitted for approval. You will be notified once it has been reviewed.');
    }

    /**
     * Show the form for editing a training.
     */
    public function edit(Request $request, Training $training): View
    {
        $this->authorizeTrainerOwnership($request, $training);

        $training->load('invitees');

        return view('trainer.trainings.edit', [
            'training' => $training,
            'trainingTypes' => TrainingType::cases(),
        ]);
    }

    /**
     * Update an existing training. Only editable when pending_approval or denied.
     */
    public function update(Request $request, Training $training): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $training);

        if (!in_array($training->status, [TrainingStatus::PendingApproval, TrainingStatus::Denied])) {
            return redirect()->route('trainer.trainings.edit', $training)
                ->with('error', 'This training cannot be edited in its current status.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', 'string', 'in:' . implode(',', array_column(TrainingType::cases(), 'value'))],
            'location_name' => ['nullable', 'string', 'max:255'],
            'location_address' => ['nullable', 'string', 'max:500'],
            'virtual_link' => ['nullable', 'url', 'max:500'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'timezone' => ['required', 'string', 'max:50'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
            'is_paid' => ['boolean'],
            'price' => ['required_if:is_paid,true', 'nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'invitees' => ['array'],
            'invitees.*' => ['nullable', 'email', 'max:255'],
        ]);

        // Group trainings are always free
        if ($training->is_group) {
            $validated['is_paid'] = false;
            $validated['price'] = null;
        }

        if (!empty($validated['price'])) {
            $validated['price_cents'] = (int) round($validated['price'] * 100);
        }
        unset($validated['price']);

        // Update invitees for group trainings
        if ($training->is_group) {
            $inviteeEmails = array_filter($validated['invitees'] ?? []);
            unset($validated['invitees']);

            // Sync invitees: remove old ones not in new list, add new ones
            $training->invitees()->whereNotIn('email', $inviteeEmails)->delete();
            $existingEmails = $training->invitees()->pluck('email')->toArray();
            foreach ($inviteeEmails as $email) {
                $normalizedEmail = strtolower(trim($email));
                if (!in_array($normalizedEmail, $existingEmails)) {
                    $training->invitees()->create(['email' => $normalizedEmail]);
                }
            }
        } else {
            unset($validated['invitees']);
        }

        // If denied, resubmit for approval
        $wasResubmitted = false;
        if ($training->status === TrainingStatus::Denied) {
            $validated['status'] = TrainingStatus::PendingApproval;
            $validated['denied_reason'] = null;
            $wasResubmitted = true;
        }

        $training->update($validated);

        // Re-notify admin on resubmission
        if ($wasResubmitted) {
            $this->safeNotifyRoute(SiteSetting::adminEmail(), new TrainingSubmittedNotification($training));
        }

        $message = $wasResubmitted
            ? 'Training updated and resubmitted for approval.'
            : 'Training updated successfully.';

        return redirect()
            ->route('trainer.trainings.edit', $training)
            ->with('success', $message);
    }

    /**
     * Soft-delete a training.
     */
    public function destroy(Request $request, Training $training): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $training);

        $training->delete();

        return redirect()
            ->route('trainer.trainings.index')
            ->with('success', 'Training deleted successfully.');
    }

    /**
     * Cancel a training (set status to canceled).
     */
    public function cancel(Request $request, Training $training): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $training);

        $training->update(['status' => TrainingStatus::Canceled]);

        return redirect()
            ->route('trainer.trainings.edit', $training)
            ->with('success', 'Training has been canceled.');
    }

    /**
     * Mark a training as completed.
     */
    public function markComplete(Request $request, Training $training): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $training);

        $training->update(['status' => TrainingStatus::Completed]);

        return redirect()
            ->route('trainer.trainings.edit', $training)
            ->with('success', 'Training has been marked as completed.');
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
}
