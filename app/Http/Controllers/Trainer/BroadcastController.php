<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\TrainerBroadcast;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\TrainerBroadcastNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastController extends Controller
{
    use SafelyNotifies;

    public function index(Request $request): View
    {
        $trainer = $request->user();

        $trainings = $trainer->trainings()
            ->whereIn('status', ['published', 'completed'])
            ->withCount(['registrations' => fn ($q) => $q->where('status', '!=', 'canceled')])
            ->orderByDesc('start_date')
            ->get();

        $broadcasts = $trainer->trainerBroadcasts()
            ->with('trainings:id,title')
            ->orderByDesc('sent_at')
            ->paginate(10);

        return view('trainer.broadcasts.index', [
            'trainings' => $trainings,
            'broadcasts' => $broadcasts,
            'preselectedTrainingId' => $request->query('training_id'),
        ]);
    }

    public function recipientCount(Request $request): JsonResponse
    {
        $request->validate([
            'training_ids' => ['required', 'array', 'min:1'],
            'training_ids.*' => ['integer', 'exists:trainings,id'],
        ]);

        $trainer = $request->user();

        // Verify ownership of all training IDs
        $ownedIds = $trainer->trainings()
            ->whereIn('id', $request->training_ids)
            ->pluck('id');

        if ($ownedIds->count() !== count($request->training_ids)) {
            return response()->json(['count' => 0], 403);
        }

        $count = \App\Models\TrainingRegistration::whereIn('training_id', $ownedIds)
            ->where('status', '!=', 'canceled')
            ->distinct('user_id')
            ->count('user_id');

        return response()->json(['count' => $count]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'training_ids' => ['required', 'array', 'min:1'],
            'training_ids.*' => ['integer', 'exists:trainings,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $trainer = $request->user();

        // Verify ownership
        $ownedIds = $trainer->trainings()
            ->whereIn('id', $validated['training_ids'])
            ->pluck('id');

        if ($ownedIds->count() !== count($validated['training_ids'])) {
            return back()->with('error', 'You can only broadcast to your own trainings.');
        }

        // Collect unique recipients (non-canceled registrants)
        $recipients = \App\Models\User::whereIn('id', function ($query) use ($ownedIds) {
            $query->select('user_id')
                ->from('training_registrations')
                ->whereIn('training_id', $ownedIds)
                ->where('status', '!=', 'canceled');
        })->get();

        if ($recipients->isEmpty()) {
            return back()->with('error', 'No recipients found for the selected trainings.');
        }

        // Create broadcast record
        $broadcast = TrainerBroadcast::create([
            'trainer_id' => $trainer->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'recipient_count' => $recipients->count(),
            'sent_at' => now(),
        ]);

        $broadcast->trainings()->attach($ownedIds);

        // Send queued notifications
        $notification = new TrainerBroadcastNotification(
            emailSubject: $validated['subject'],
            emailBody: $validated['body'],
            trainerName: $trainer->full_name,
        );

        foreach ($recipients as $recipient) {
            $this->safeNotify($recipient, $notification);
        }

        return redirect()
            ->route('trainer.broadcasts.index')
            ->with('success', "Broadcast sent to {$recipients->count()} recipient(s).");
    }
}
