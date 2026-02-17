<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Clinical;
use App\Notifications\CertificateReadyNotification;
use App\Notifications\Concerns\SafelyNotifies;
use App\Services\CertificateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalController extends Controller
{
    use SafelyNotifies;

    public function __construct(
        protected CertificateService $certificateService,
    ) {}

    /**
     * List clinicals assigned to the authenticated trainer.
     */
    public function index(Request $request): View
    {
        $clinicals = Clinical::where('trainer_id', $request->user()->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('trainer.clinicals.index', compact('clinicals'));
    }

    /**
     * Show a single clinical submission detail.
     */
    public function show(Request $request, Clinical $clinical): View
    {
        $this->authorizeTrainerOwnership($request, $clinical);

        $clinical->load(['user', 'reviewer']);

        $hasCertificate = $clinical->user->certificates()->exists();

        return view('trainer.clinicals.show', compact('clinical', 'hasCertificate'));
    }

    /**
     * Approve a clinical submission.
     */
    public function approve(Request $request, Clinical $clinical): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $clinical);

        if (! in_array($clinical->status, ['submitted', 'under_review'])) {
            return redirect()
                ->route('trainer.clinicals.show', $clinical)
                ->with('error', 'This clinical cannot be approved from its current status.');
        }

        $clinical->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('trainer.clinicals.show', $clinical)
            ->with('success', 'Clinical submission approved.');
    }

    /**
     * Reject a clinical submission.
     */
    public function reject(Request $request, Clinical $clinical): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $clinical);

        if (! in_array($clinical->status, ['submitted', 'under_review'])) {
            return redirect()
                ->route('trainer.clinicals.show', $clinical)
                ->with('error', 'This clinical cannot be rejected from its current status.');
        }

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:2000'],
        ]);

        $clinical->update([
            'status' => 'rejected',
            'notes' => $validated['notes'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('trainer.clinicals.show', $clinical)
            ->with('success', 'Clinical submission rejected.');
    }

    /**
     * Issue a certificate for an approved clinical submission.
     */
    public function issueCertificate(Request $request, Clinical $clinical): RedirectResponse
    {
        $this->authorizeTrainerOwnership($request, $clinical);

        if ($clinical->status !== 'approved') {
            return redirect()
                ->route('trainer.clinicals.show', $clinical)
                ->with('error', 'Clinical must be approved before issuing a certificate.');
        }

        if ($clinical->user->certificates()->exists()) {
            return redirect()
                ->route('trainer.clinicals.show', $clinical)
                ->with('error', 'This member already has a certificate.');
        }

        // Find the training from the member's completed registration
        $registration = $clinical->user->trainingRegistrations()
            ->where('status', \App\Enums\RegistrationStatus::Completed)
            ->first();

        $certificate = $this->certificateService->issueCertificate(
            user: $clinical->user,
            training: $registration?->training,
            issuedBy: $request->user(),
        );

        if ($registration) {
            $registration->update(['certificate_id' => $certificate->id]);
        }

        $this->safeNotify($clinical->user, new CertificateReadyNotification($certificate));

        return redirect()
            ->route('trainer.clinicals.show', $clinical)
            ->with('success', "Certificate issued for {$clinical->user->full_name} (Code: {$certificate->certificate_code}).");
    }

    /**
     * Verify the authenticated trainer owns this clinical.
     */
    protected function authorizeTrainerOwnership(Request $request, Clinical $clinical): void
    {
        if ($clinical->trainer_id !== $request->user()->id) {
            abort(403, 'You do not have permission to manage this clinical submission.');
        }
    }
}
