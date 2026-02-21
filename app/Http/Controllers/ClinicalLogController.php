<?php

namespace App\Http\Controllers;

use App\Enums\ClinicalLogStatus;
use App\Models\ClinicalLog;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\ClinicalLogCompletedNotification;
use App\Notifications\Concerns\SafelyNotifies;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalLogController extends Controller
{
    use SafelyNotifies;

    public function index(Request $request): View
    {
        $logs = $request->user()
            ->clinicalLogs()
            ->with('trainer:id,first_name,last_name')
            ->withCount('entries')
            ->withSum('entries', 'hours')
            ->orderByDesc('created_at')
            ->get();

        $hasLegacyClinicals = $request->user()->clinicals()->exists();
        $threshold = (float) SiteSetting::get('clinical_hours_threshold', '40');

        return view('clinical-logs.index', compact('logs', 'hasLegacyClinicals', 'threshold'));
    }

    public function create(): View
    {
        $trainers = User::role('registered_trainer')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('clinical-logs.create', compact('trainers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trainer_id' => ['nullable', 'exists:users,id'],
        ]);

        $log = ClinicalLog::create([
            'user_id' => $request->user()->id,
            'trainer_id' => $validated['trainer_id'] ?: null,
            'status' => ClinicalLogStatus::InProgress,
        ]);

        return redirect()->route('clinical-logs.show', $log)
            ->with('success', 'Log book created. Start adding your clinical entries below.');
    }

    public function show(Request $request, ClinicalLog $log): View
    {
        $this->authorizeMember($request, $log);

        $log->load(['trainer:id,first_name,last_name', 'entries' => fn ($q) => $q->orderBy('date')]);

        $trainers = User::role('registered_trainer')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        $threshold = (float) SiteSetting::get('clinical_hours_threshold', '40');

        return view('clinical-logs.show', compact('log', 'trainers', 'threshold'));
    }

    public function update(Request $request, ClinicalLog $log): RedirectResponse
    {
        $this->authorizeMember($request, $log);

        if ($log->status !== ClinicalLogStatus::InProgress) {
            return redirect()->route('clinical-logs.show', $log)
                ->with('error', 'Cannot update a log that is not in progress.');
        }

        $validated = $request->validate([
            'trainer_id' => ['nullable', 'exists:users,id'],
        ]);

        $log->update([
            'trainer_id' => $validated['trainer_id'] ?: null,
        ]);

        return redirect()->route('clinical-logs.show', $log)
            ->with('success', 'Trainer assignment updated.');
    }

    public function markComplete(Request $request, ClinicalLog $log): RedirectResponse
    {
        $this->authorizeMember($request, $log);

        if ($log->status !== ClinicalLogStatus::InProgress) {
            return redirect()->route('clinical-logs.show', $log)
                ->with('error', 'This log book cannot be marked complete from its current status.');
        }

        if (! $log->meets_threshold) {
            return redirect()->route('clinical-logs.show', $log)
                ->with('error', 'You must reach the required hours threshold before marking this log complete.');
        }

        $log->update([
            'status' => ClinicalLogStatus::Completed,
            'completed_at' => now(),
        ]);

        // Notify trainer or admin
        if ($log->trainer) {
            $this->safeNotify($log->trainer, new ClinicalLogCompletedNotification($log));
        } else {
            $this->safeNotifyRoute(SiteSetting::adminEmail(), new ClinicalLogCompletedNotification($log));
        }

        return redirect()->route('clinical-logs.show', $log)
            ->with('success', 'Log book marked as complete and submitted for review.');
    }

    public function destroy(Request $request, ClinicalLog $log): RedirectResponse
    {
        $this->authorizeMember($request, $log);

        if ($log->status !== ClinicalLogStatus::InProgress) {
            return redirect()->route('clinical-logs.show', $log)
                ->with('error', 'Only in-progress log books can be deleted.');
        }

        if ($log->entries()->exists()) {
            return redirect()->route('clinical-logs.show', $log)
                ->with('error', 'Cannot delete a log book that has entries. Remove all entries first.');
        }

        $log->delete();

        return redirect()->route('clinical-logs.index')
            ->with('success', 'Log book deleted.');
    }

    protected function authorizeMember(Request $request, ClinicalLog $log): void
    {
        if ($log->user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to access this log book.');
        }
    }
}
