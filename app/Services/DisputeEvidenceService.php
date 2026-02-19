<?php

namespace App\Services;

use App\Models\AgreementSignature;
use Barryvdh\DomPDF\Facade\Pdf;

class DisputeEvidenceService
{
    public function generate(AgreementSignature $signature): \Barryvdh\DomPDF\PDF
    {
        $signature->load(['user', 'agreement']);

        $contextReference = $this->resolveContextReference($signature);

        $html = $this->buildHtml($signature, $contextReference);

        return Pdf::loadHTML($html)->setPaper('a4');
    }

    protected function resolveContextReference(AgreementSignature $signature): ?string
    {
        if (! $signature->context_reference_type || ! $signature->context_reference_id) {
            return null;
        }

        $model = $signature->context_reference_type::find($signature->context_reference_id);

        if (! $model) {
            return null;
        }

        return match ($signature->context_reference_type) {
            'App\Models\Plan' => $model->name,
            'App\Models\Training' => $model->title . ' (' . $model->start_date->format('M j, Y') . ')',
            default => 'ID: ' . $signature->context_reference_id,
        };
    }

    protected function getLogoBase64(): string
    {
        $logoPath = public_path('images/nada-mark.png');

        if (! file_exists($logoPath)) {
            return '';
        }

        $data = base64_encode(file_get_contents($logoPath));

        return 'data:image/png;base64,' . $data;
    }

    protected function buildHtml(AgreementSignature $signature, ?string $contextReference): string
    {
        $user = $signature->user;
        $agreement = $signature->agreement;
        $logoSrc = $this->getLogoBase64();

        $contextLabel = match ($signature->consent_context) {
            'membership_subscription' => 'Membership Subscription',
            'plan_switch' => 'Plan Switch',
            'training_registration' => 'Training Registration',
            'trainer_application' => 'Trainer Application',
            default => $signature->consent_context ?? 'N/A',
        };

        $signedAt = $signature->signed_at->format('F j, Y \a\t g:i:s A T');
        $generatedAt = now()->format('F j, Y \a\t g:i:s A T');

        $logoHtml = $logoSrc
            ? '<img src="' . $logoSrc . '" style="height: 60px; margin-right: 16px;">'
            : '';

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; line-height: 1.6; margin: 40px; }
                .header { display: table; width: 100%; border-bottom: 3px solid #1C3519; padding-bottom: 12px; margin-bottom: 24px; }
                .header-logo { display: table-cell; vertical-align: middle; width: 80px; }
                .header-text { display: table-cell; vertical-align: middle; }
                .header-title { color: #1C3519; font-size: 20px; font-weight: bold; margin: 0; }
                .header-subtitle { color: #AD7E07; font-size: 12px; margin: 2px 0 0 0; }
                .header-doc-type { color: #777; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; margin: 4px 0 0 0; }
                h2 { color: #1C3519; font-size: 14px; margin-top: 24px; border-bottom: 1px solid #e5e5e5; padding-bottom: 4px; }
                .section { margin-bottom: 20px; }
                .field { margin-bottom: 6px; }
                .label { font-weight: bold; color: #555; display: inline-block; width: 140px; }
                .value { color: #111; }
                .snapshot { border: 1px solid #ddd; padding: 16px; background: #fafafa; margin-top: 12px; font-size: 11px; }
                .footer { margin-top: 40px; padding-top: 12px; border-top: 2px solid #1C3519; font-size: 10px; color: #777; text-align: center; }
                .footer strong { color: #1C3519; }
                .badge { display: inline-block; background: #1C3519; color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 3px; text-transform: uppercase; letter-spacing: 0.5px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="header-logo">{$logoHtml}</div>
                <div class="header-text">
                    <p class="header-title">National Acupuncture Detoxification Association</p>
                    <p class="header-subtitle">NADA Membership Portal</p>
                    <p class="header-doc-type">Consent Evidence Document &bull; Signature #{$signature->id}</p>
                </div>
            </div>

            <div class="section">
                <h2>User Information</h2>
                <div class="field"><span class="label">Name:</span> <span class="value">{$this->e($user->full_name)}</span></div>
                <div class="field"><span class="label">Email:</span> <span class="value">{$this->e($user->email)}</span></div>
                <div class="field"><span class="label">User ID:</span> <span class="value">{$user->id}</span></div>
            </div>

            <div class="section">
                <h2>Consent Details</h2>
                <div class="field"><span class="label">Agreement:</span> <span class="value">{$this->e($agreement->title)} (v{$agreement->version})</span></div>
                <div class="field"><span class="label">Signature ID:</span> <span class="value">{$signature->id}</span></div>
                <div class="field"><span class="label">Signed At:</span> <span class="value">{$signedAt}</span></div>
                <div class="field"><span class="label">IP Address:</span> <span class="value">{$this->e($signature->ip_address ?? 'N/A')}</span></div>
                <div class="field"><span class="label">User Agent:</span> <span class="value">{$this->e($signature->user_agent ?? 'N/A')}</span></div>
                <div class="field"><span class="label">Consent Context:</span> <span class="value">{$this->e($contextLabel)}</span></div>
                <div class="field"><span class="label">Context Reference:</span> <span class="value">{$this->e($contextReference ?? 'N/A')}</span></div>
            </div>

            <div class="section">
                <h2>Terms &amp; Conditions (Snapshot at Time of Consent)</h2>
                <div class="snapshot">{$signature->consent_snapshot}</div>
            </div>

            <div class="footer">
                <strong>National Acupuncture Detoxification Association</strong><br>
                This document serves as official proof of user consent for payment dispute resolution.<br>
                Generated {$generatedAt}
            </div>
        </body>
        </html>
        HTML;
    }

    protected function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
