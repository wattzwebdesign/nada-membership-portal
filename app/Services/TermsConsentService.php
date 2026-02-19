<?php

namespace App\Services;

use App\Models\Agreement;
use App\Models\AgreementSignature;
use App\Models\User;
use Illuminate\Http\Request;

class TermsConsentService
{
    public function getActiveTerms(): ?Agreement
    {
        return Agreement::getActiveTerms();
    }

    public function recordConsent(
        Request $request,
        User $user,
        string $context,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): AgreementSignature {
        $terms = $this->getActiveTerms();

        if (! $terms) {
            throw new \RuntimeException('No active Terms & Conditions agreement found.');
        }

        return AgreementSignature::create([
            'user_id' => $user->id,
            'agreement_id' => $terms->id,
            'signed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'consent_context' => $context,
            'context_reference_type' => $referenceType,
            'context_reference_id' => $referenceId,
            'consent_snapshot' => $terms->content,
        ]);
    }

    public function stripeMetadata(AgreementSignature $signature): array
    {
        return [
            'tc_signature_id' => $signature->id,
            'tc_agreement_id' => $signature->agreement_id,
            'tc_version' => $signature->agreement->version,
            'tc_accepted_at' => $signature->signed_at->toIso8601String(),
            'tc_ip_address' => $signature->ip_address,
        ];
    }
}
