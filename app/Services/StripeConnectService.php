<?php

namespace App\Services;

use App\Models\PayoutSetting;
use App\Models\StripeAccount;
use App\Models\User;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Balance;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Transfer;

class StripeConnectService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createExpressAccount(User $user): Account
    {
        $account = Account::create([
            'type' => 'express',
            'email' => $user->email,
            'metadata' => ['user_id' => $user->id],
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);

        StripeAccount::create([
            'user_id' => $user->id,
            'stripe_connect_account_id' => $account->id,
        ]);

        return $account;
    }

    public function createOnboardingLink(string $accountId, string $refreshUrl, string $returnUrl): AccountLink
    {
        return AccountLink::create([
            'account' => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);
    }

    public function createLoginLink(string $accountId): \Stripe\LoginLink
    {
        return Account::createLoginLink($accountId);
    }

    public function retrieveAccount(string $accountId): Account
    {
        return Account::retrieve($accountId);
    }

    public function syncAccountStatus(StripeAccount $stripeAccount): void
    {
        $account = $this->retrieveAccount($stripeAccount->stripe_connect_account_id);

        $stripeAccount->update([
            'charges_enabled' => $account->charges_enabled,
            'payouts_enabled' => $account->payouts_enabled,
            'details_submitted' => $account->details_submitted,
            'onboarding_complete' => $account->charges_enabled && $account->payouts_enabled,
        ]);
    }

    public function createPaymentWithSplit(
        int $amountCents,
        string $currency,
        string $connectedAccountId,
        int $trainerId,
        array $metadata = []
    ): PaymentIntent {
        $settings = PayoutSetting::getForTrainer($trainerId);
        $applicationFee = (int) round($amountCents * ($settings->platform_percentage / 100));

        return PaymentIntent::create([
            'amount' => $amountCents,
            'currency' => $currency,
            'application_fee_amount' => $applicationFee,
            'transfer_data' => [
                'destination' => $connectedAccountId,
            ],
            'metadata' => $metadata,
        ]);
    }

    public function createTransfer(int $amountCents, string $connectedAccountId, string $currency = 'usd'): Transfer
    {
        return Transfer::create([
            'amount' => $amountCents,
            'currency' => $currency,
            'destination' => $connectedAccountId,
        ]);
    }

    public function getBalance(string $connectedAccountId): Balance
    {
        return Balance::retrieve(['stripe_account' => $connectedAccountId]);
    }
}
