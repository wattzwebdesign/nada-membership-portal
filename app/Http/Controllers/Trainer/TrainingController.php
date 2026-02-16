<?php

namespace App\Http\Controllers\Trainer;

use App\Enums\TrainingStatus;
use App\Enums\TrainingType;
use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
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
     * Store a newly created training.
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
        ]);

        if (!empty($validated['price'])) {
            $validated['price_cents'] = (int) round($validated['price'] * 100);
        }
        unset($validated['price']);

        $validated['trainer_id'] = $request->user()->id;
        $validated['status'] = TrainingStatus::Draft;

        $training = Training::create($validated);

        return redirect()
            ->route('trainer.trainings.edit', $training)
            ->with('success', 'Training created successfully. You can publish it when ready.');
    }

    /**
     * Show the form for editing a training.
     */
    public function edit(Request $request, Training $training): View
    {
        $this->authorizeTrainerOwnership($request, $training);

        return view('trainer.trainings.edit', [
            'training' => $training,
            'trainingTypes' => TrainingType::cases(),
        ]);
    }

    /**
     * Update an existing training.
     */
    public function update(Request $request, Training $training): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $training);

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
        ]);

        if (!empty($validated['price'])) {
            $validated['price_cents'] = (int) round($validated['price'] * 100);
        }
        unset($validated['price']);

        $training->update($validated);

        return redirect()
            ->route('trainer.trainings.edit', $training)
            ->with('success', 'Training updated successfully.');
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
     * Publish a training (set status to published).
     */
    public function publish(Request $request, Training $training): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $training);

        $training->update(['status' => TrainingStatus::Published]);

        return redirect()
            ->route('trainer.trainings.edit', $training)
            ->with('success', 'Training has been published and is now visible to members.');
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
