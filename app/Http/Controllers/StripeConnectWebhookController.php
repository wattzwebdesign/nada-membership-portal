<?php

namespace App\Http\Controllers;

use App\Models\StripeAccount;
use App\Models\User;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\PayoutReceivedNotification;
use App\Services\StripeConnectService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeConnectWebhookController extends Controller
{
    use SafelyNotifies;
    public function __construct(
        protected StripeConnectService $stripeConnectService,
    ) {}

    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.connect_webhook_secret')
            );
        } catch (\Exception $e) {
            Log::error('Stripe Connect webhook signature verification failed.', [
                'error' => $e->getMessage(),
            ]);

            return response('Invalid signature', 400);
        }

        $method = 'handle' . str_replace('.', '', ucwords(str_replace('_', '.', $event->type), '.'));

        Log::info("Stripe Connect webhook received: {$event->type}", [
            'event_id' => $event->id,
            'account' => $event->account ?? null,
        ]);

        if (method_exists($this, $method)) {
            try {
                return $this->$method($event->data->object, $event->account ?? null);
            } catch (\Exception $e) {
                Log::error("Stripe Connect webhook handler [{$method}] failed.", [
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response('Webhook handler error', 500);
            }
        }

        return response('Unhandled event type', 200);
    }

    /**
     * Handle account.updated - a connected account's details were updated.
     *
     * This fires when onboarding completes, capabilities change, or the
     * account holder updates their information.
     */
    protected function handleAccountUpdated(object $account, ?string $connectedAccountId): Response
    {
        $accountId = $account->id ?? $connectedAccountId;

        if (! $accountId) {
            Log::warning('Stripe Connect webhook: no account ID found for account.updated.');

            return response('No account ID', 200);
        }

        $stripeAccount = StripeAccount::where('stripe_connect_account_id', $accountId)->first();

        if (! $stripeAccount) {
            Log::warning('Stripe Connect webhook: StripeAccount record not found for account.updated.', [
                'stripe_connect_account_id' => $accountId,
            ]);

            return response('Account not found', 200);
        }

        $stripeAccount->update([
            'charges_enabled' => $account->charges_enabled ?? $stripeAccount->charges_enabled,
            'payouts_enabled' => $account->payouts_enabled ?? $stripeAccount->payouts_enabled,
            'details_submitted' => $account->details_submitted ?? $stripeAccount->details_submitted,
            'default_currency' => $account->default_currency ?? $stripeAccount->default_currency,
            'onboarding_complete' => ($account->charges_enabled ?? false)
                && ($account->payouts_enabled ?? false),
        ]);

        Log::info('Connected account updated from webhook.', [
            'user_id' => $stripeAccount->user_id,
            'stripe_connect_account_id' => $accountId,
            'charges_enabled' => $account->charges_enabled ?? null,
            'payouts_enabled' => $account->payouts_enabled ?? null,
            'details_submitted' => $account->details_submitted ?? null,
            'onboarding_complete' => ($account->charges_enabled ?? false)
                && ($account->payouts_enabled ?? false),
        ]);

        return response('Account updated', 200);
    }

    /**
     * Handle payout.paid - a payout to a connected account's bank completed.
     */
    protected function handlePayoutPaid(object $payout, ?string $connectedAccountId): Response
    {
        $stripeAccount = $this->resolveStripeAccount($connectedAccountId);

        $amountFormatted = number_format(($payout->amount ?? 0) / 100, 2);
        $currency = strtoupper($payout->currency ?? 'USD');

        if ($stripeAccount) {
            Log::info('Payout paid to connected account.', [
                'user_id' => $stripeAccount->user_id,
                'stripe_connect_account_id' => $connectedAccountId,
                'payout_id' => $payout->id,
                'amount' => $amountFormatted,
                'currency' => $currency,
                'arrival_date' => $payout->arrival_date ?? null,
            ]);

            // Notify the trainer that their payout arrived.
            $user = $stripeAccount->user;
            if ($user) {
                $this->safeNotify($user, new PayoutReceivedNotification(
                    amount: ($payout->amount ?? 0) / 100,
                    currency: $currency,
                ));

                Log::info("Payout of {$currency} {$amountFormatted} arrived for trainer {$user->full_name}.", [
                    'user_id' => $user->id,
                    'payout_id' => $payout->id,
                ]);
            }
        } else {
            Log::warning('Stripe Connect webhook: StripeAccount not found for payout.paid.', [
                'stripe_connect_account_id' => $connectedAccountId,
                'payout_id' => $payout->id,
                'amount' => $amountFormatted,
                'currency' => $currency,
            ]);
        }

        return response('Payout paid logged', 200);
    }

    /**
     * Handle payout.failed - a payout to a connected account's bank failed.
     */
    protected function handlePayoutFailed(object $payout, ?string $connectedAccountId): Response
    {
        $stripeAccount = $this->resolveStripeAccount($connectedAccountId);

        $amountFormatted = number_format(($payout->amount ?? 0) / 100, 2);
        $currency = strtoupper($payout->currency ?? 'USD');
        $failureCode = $payout->failure_code ?? 'unknown';
        $failureMessage = $payout->failure_message ?? 'No failure message provided.';

        if ($stripeAccount) {
            $user = $stripeAccount->user;

            Log::error('Payout failed for connected account.', [
                'user_id' => $stripeAccount->user_id,
                'trainer_name' => $user?->full_name,
                'stripe_connect_account_id' => $connectedAccountId,
                'payout_id' => $payout->id,
                'amount' => $amountFormatted,
                'currency' => $currency,
                'failure_code' => $failureCode,
                'failure_message' => $failureMessage,
            ]);

            // Notify admins about the failed payout.
            $this->notifyAdminsOfPayoutFailure($payout, $stripeAccount, $user);
        } else {
            Log::error('Payout failed but StripeAccount not found.', [
                'stripe_connect_account_id' => $connectedAccountId,
                'payout_id' => $payout->id,
                'amount' => $amountFormatted,
                'currency' => $currency,
                'failure_code' => $failureCode,
                'failure_message' => $failureMessage,
            ]);

            // Still notify admins even if we cannot resolve the local account.
            $this->notifyAdminsOfPayoutFailure($payout, null, null);
        }

        return response('Payout failure logged', 200);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Resolve the local StripeAccount from a connected account ID.
     */
    protected function resolveStripeAccount(?string $connectedAccountId): ?StripeAccount
    {
        if (! $connectedAccountId) {
            return null;
        }

        return StripeAccount::where('stripe_connect_account_id', $connectedAccountId)->first();
    }

    /**
     * Notify admin users about a payout failure so they can investigate.
     */
    protected function notifyAdminsOfPayoutFailure(
        object $payout,
        ?StripeAccount $stripeAccount,
        ?User $trainer,
    ): void {
        $amountFormatted = number_format(($payout->amount ?? 0) / 100, 2);
        $currency = strtoupper($payout->currency ?? 'USD');

        $context = [
            'payout_id' => $payout->id,
            'amount' => "{$currency} {$amountFormatted}",
            'failure_code' => $payout->failure_code ?? 'unknown',
            'failure_message' => $payout->failure_message ?? 'No failure message provided.',
            'trainer' => $trainer ? "{$trainer->full_name} (ID: {$trainer->id})" : 'Unknown',
            'stripe_connect_account_id' => $stripeAccount?->stripe_connect_account_id ?? 'Unknown',
        ];

        // Log at the error level so monitoring picks it up immediately.
        Log::error('ADMIN ALERT: Payout failed for connected account. Manual investigation required.', $context);

        // If a Notification class is available in the future, send it here:
        // $admins = User::role('admin')->get();
        // Notification::send($admins, new PayoutFailedNotification($context));
    }
}
