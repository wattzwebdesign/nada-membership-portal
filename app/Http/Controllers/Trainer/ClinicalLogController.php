<?php

namespace App\Http\Controllers\Trainer;

use App\Enums\ClinicalLogStatus;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\ClinicalLog;
use App\Notifications\CertificateReadyNotification;
use App\Notifications\ClinicalLogApprovedNotification;
use App\Notifications\ClinicalLogRejectedNotification;
use App\Notifications\Concerns\SafelyNotifies;
use App\Services\CertificateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalLogController extends Controller
{
    use SafelyNotifies;

    public function __construct(
        protected CertificateService $certificateService,
    ) {}

    public function index(Request $request): View
    {
        $logs = ClinicalLog::forTrainer($request->user()->id)
            ->with('user:id,first_name,last_name,email')
            ->withSum('entries', 'hours')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('trainer.clinical-logs.index', compact('logs'));
    }

    public function show(Request $request, ClinicalLog $log): View
    {
        $this->authorizeTrainerOwnership($request, $log);

        $log->load(['user', 'reviewer', 'entries' => fn ($q) => $q->orderBy('date')]);

        // Load media for each entry
        $log->entries->each(fn ($entry) => $entry->load('media'));

        $hasCertificate = $log->user->certificates()->exists();

        return view('trainer.clinical-logs.show', compact('log', 'hasCertificate'));
    }

    public function approve(Request $request, ClinicalLog $log): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $log);

        if ($log->status !== ClinicalLogStatus::Completed) {
            return redirect()->route('trainer.clinical-logs.show', $log)
                ->with('error', 'Only completed log books can be approved.');
        }

        $log->update([
            'status' => ClinicalLogStatus::Approved,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $this->safeNotify($log->user, new ClinicalLogApprovedNotification($log));

        return redirect()->route('trainer.clinical-logs.show', $log)
            ->with('success', 'Clinical log book approved.');
    }

    public function reject(Request $request, ClinicalLog $log): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $log);

        if ($log->status !== ClinicalLogStatus::Completed) {
            return redirect()->route('trainer.clinical-logs.show', $log)
                ->with('error', 'Only completed log books can be rejected.');
        }

        $validated = $request->validate([
            'reviewer_notes' => ['required', 'string', 'max:2000'],
        ]);

        $log->update([
            'status' => ClinicalLogStatus::InProgress,
            'reviewer_notes' => $validated['reviewer_notes'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'completed_at' => null,
        ]);

        $this->safeNotify($log->user, new ClinicalLogRejectedNotification($log));

        return redirect()->route('trainer.clinical-logs.show', $log)
            ->with('success', 'Clinical log book returned to member for revision.');
    }

    public function issueCertificate(Request $request, ClinicalLog $log): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $log);

        if ($log->status !== ClinicalLogStatus::Approved) {
            return redirect()->route('trainer.clinical-logs.show', $log)
                ->with('error', 'Log book must be approved before issuing a certificate.');
        }

        if ($log->user->certificates()->exists()) {
            return redirect()->route('trainer.clinical-logs.show', $log)
                ->with('error', 'This member already has a certificate.');
        }

        $registration = $log->user->trainingRegistrations()
            ->where('status', RegistrationStatus::Completed)
            ->first();

        $certificate = $this->certificateService->issueCertificate(
            user: $log->user,
            training: $registration?->training,
            issuedBy: $request->user(),
        );

        if ($registration) {
            $registration->update(['certificate_id' => $certificate->id]);
        }

        $this->safeNotify($log->user, new CertificateReadyNotification($certificate));

        return redirect()->route('trainer.clinical-logs.show', $log)
            ->with('success', "Certificate issued for {$log->user->full_name} (Code: {$certificate->certificate_code}).");
    }

    protected function authorizeTrainerOwnership(Request $request, ClinicalLog $log): void
    {
        if ($log->trainer_id !== $request->user()->id) {
            abort(403, 'You do not have permission to manage this clinical log book.');
        }
    }
}
