<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use App\Models\Subscription;
use App\Services\CertificateService;
use Illuminate\Console\Command;

class SyncCertificateExpirations extends Command
{
    protected $signature = 'nada:sync-certificate-expirations';
    protected $description = 'Sync certificate expiration dates with subscription periods';

    public function handle(CertificateService $certificateService): int
    {
        $this->info('Syncing certificate expirations...');

        $synced = 0;
        $expired = 0;

        // Find active certificates and sync with subscription
        $certificates = Certificate::where('status', 'active')->with('user.activeSubscription')->get();

        foreach ($certificates as $certificate) {
            $subscription = $certificate->user?->activeSubscription;

            if (!$subscription || $subscription->status->value !== 'active') {
                // No active subscription - expire the certificate
                if ($certificate->expiration_date && $certificate->expiration_date->isPast()) {
                    $certificate->update(['status' => 'expired']);
                    $expired++;
                }
                continue;
            }

            // Sync expiration date
            if ($subscription->current_period_end &&
                ($certificate->expiration_date === null ||
                 $certificate->expiration_date->toDateString() !== $subscription->current_period_end->toDateString())) {
                $certificate->update(['expiration_date' => $subscription->current_period_end->toDateString()]);
                $synced++;
            }
        }

        $this->info("Synced: {$synced}, Expired: {$expired}");
        return Command::SUCCESS;
    }
}
