<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class WalletPassService
{
    public function __construct(
        protected AppleWalletService $appleWalletService,
        protected GoogleWalletService $googleWalletService,
    ) {}

    public function generateApplePass(User $user): string
    {
        return $this->appleWalletService->createPass($user);
    }

    public function generateGooglePassUrl(User $user): string
    {
        return $this->googleWalletService->createPassAndGetSaveUrl($user);
    }

    public function updateAllPassesForUser(User $user): void
    {
        $passes = $user->walletPasses;

        foreach ($passes as $pass) {
            try {
                if ($pass->platform === 'apple') {
                    $this->appleWalletService->pushUpdateToDevices($pass);
                } elseif ($pass->platform === 'google') {
                    $this->googleWalletService->updatePassObject($pass, $user);
                }
            } catch (\Exception $e) {
                Log::error('Failed to update wallet pass.', [
                    'wallet_pass_id' => $pass->id,
                    'platform' => $pass->platform,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function onCertificateIssued(User $user): void
    {
        $this->updateAllPassesForUser($user);
    }

    public function buildPassData(User $user): array
    {
        $user->load(['activeSubscription.plan', 'certificates']);

        $subscription = $user->activeSubscription;
        $activeCert = $user->certificates
            ->where('status', 'active')
            ->sortByDesc('date_issued')
            ->first();

        $expiryDate = $subscription?->current_period_end;

        return [
            'name' => $user->full_name,
            'plan' => $subscription?->plan?->name ?? 'No Active Plan',
            'expiry' => $expiryDate ? $expiryDate->format('M j, Y') : 'N/A',
            'certificate_code' => $activeCert?->certificate_code,
            'verify_url' => $activeCert
                ? config('app.url') . '/verify/' . $activeCert->certificate_code
                : null,
            'member_since' => $user->created_at->format('M j, Y'),
        ];
    }
}
