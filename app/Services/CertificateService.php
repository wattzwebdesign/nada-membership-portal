<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Training;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    public function generateCode(): string
    {
        $prefix = config('app.nada_cert_code_prefix', 'NADA');
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(5));

        $code = "{$prefix}-{$date}-{$random}";

        while (Certificate::where('certificate_code', $code)->exists()) {
            $random = strtoupper(Str::random(5));
            $code = "{$prefix}-{$date}-{$random}";
        }

        return $code;
    }

    public function issueCertificate(
        User $user,
        ?Training $training = null,
        ?User $issuedBy = null,
        ?string $code = null
    ): Certificate {
        $subscription = $user->activeSubscription;
        $expirationDate = $subscription?->current_period_end;

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'certificate_code' => $code ?? $this->generateCode(),
            'training_id' => $training?->id,
            'issued_by' => $issuedBy?->id,
            'date_issued' => now()->toDateString(),
            'expiration_date' => $expirationDate?->toDateString(),
            'status' => 'active',
        ]);

        $this->generatePdf($certificate);

        return $certificate;
    }

    public function generatePdf(Certificate $certificate): string
    {
        $certificate->load('user');

        $html = view('certificates.template', [
            'certificate' => $certificate,
        ])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('letter', 'landscape')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = str_replace(' ', '_', $certificate->user->full_name)
            . '_NADA_Certificate.pdf';

        $path = "certificates/{$certificate->id}/{$filename}";
        Storage::put($path, $pdf->output());

        $certificate->update(['pdf_path' => $path]);

        return $path;
    }

    public function syncExpirationFromSubscription(User $user): void
    {
        $subscription = $user->activeSubscription;
        if (!$subscription) {
            return;
        }

        $user->certificates()
            ->where('status', 'active')
            ->update(['expiration_date' => $subscription->current_period_end->toDateString()]);

        // Regenerate PDFs for active certificates
        $user->certificates()->where('status', 'active')->each(function ($cert) {
            $this->generatePdf($cert);
        });
    }

    public function expireCertificatesForUser(User $user): void
    {
        $user->certificates()
            ->where('status', 'active')
            ->update(['status' => 'expired']);
    }

    public function revokeCertificate(Certificate $certificate): void
    {
        $certificate->update(['status' => 'revoked']);
    }

    public function reactivateCertificate(Certificate $certificate, ?string $newExpiration = null): void
    {
        $certificate->update([
            'status' => 'active',
            'expiration_date' => $newExpiration ?? $certificate->expiration_date,
        ]);
    }
}
