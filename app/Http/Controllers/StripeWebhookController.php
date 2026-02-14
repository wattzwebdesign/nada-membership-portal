<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\SubscriptionCanceledNotification;
use App\Notifications\SubscriptionConfirmedNotification;
use App\Notifications\SubscriptionRenewedNotification;
use App\Services\CertificateService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected CertificateService $certificateService,
    ) {}

    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed.', [
                'error' => $e->getMessage(),
            ]);

            return response('Invalid signature', 400);
        }

        $method = 'handle' . str_replace('.', '', ucwords(str_replace('_', '.', $event->type), '.'));

        Log::info("Stripe webhook received: {$event->type}", [
            'event_id' => $event->id,
        ]);

        if (method_exists($this, $method)) {
            try {
                return $this->$method($event->data->object);
            } catch (\Exception $e) {
                Log::error("Stripe webhook handler [{$method}] failed.", [
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
     * Handle customer.subscription.created - a new subscription was created.
     */
    protected function handleCustomerSubscriptionCreated(object $subscription): Response
    {
        $user = $this->resolveUserFromCustomer($subscription->customer);

        if (! $user) {
            Log::warning('Stripe webhook: user not found for customer.subscription.created.', [
                'stripe_customer_id' => $subscription->customer,
                'stripe_subscription_id' => $subscription->id,
            ]);

            return response('User not found', 200);
        }

        $localSubscription = $this->subscriptionService->createFromStripe(
            $this->objectToArray($subscription),
            $user
        );

        if ($localSubscription) {
            $user->notify(new SubscriptionConfirmedNotification($localSubscription));
        }

        // Set the subscription's payment method as the customer's default
        $paymentMethodId = $subscription->default_payment_method ?? null;
        if ($paymentMethodId && $user->stripe_customer_id) {
            try {
                \Stripe\Customer::update($user->stripe_customer_id, [
                    'invoice_settings' => ['default_payment_method' => $paymentMethodId],
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to set default payment method from subscription.', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Subscription created from webhook.', [
            'user_id' => $user->id,
            'stripe_subscription_id' => $subscription->id,
            'status' => $subscription->status,
        ]);

        return response('Subscription created', 200);
    }

    /**
     * Handle customer.subscription.updated - subscription was changed (plan swap, status change, renewal, etc.).
     */
    protected function handleCustomerSubscriptionUpdated(object $subscription): Response
    {
        $existingSub = $this->subscriptionService->updateFromStripe(
            $this->objectToArray($subscription)
        );

        if (! $existingSub) {
            Log::warning('Stripe webhook: subscription not found for customer.subscription.updated.', [
                'stripe_subscription_id' => $subscription->id,
            ]);

            return response('Subscription not found', 200);
        }

        $user = $existingSub->user;

        // If the subscription became active (e.g. after renewal), sync certificate expiration dates.
        if ($subscription->status === 'active') {
            $user->load('activeSubscription');
            $this->certificateService->syncExpirationFromSubscription($user);

            $user->notify(new SubscriptionRenewedNotification($existingSub));
        }

        Log::info('Subscription updated from webhook.', [
            'user_id' => $user->id,
            'stripe_subscription_id' => $subscription->id,
            'status' => $subscription->status,
        ]);

        return response('Subscription updated', 200);
    }

    /**
     * Handle customer.subscription.deleted - subscription was canceled/expired.
     */
    protected function handleCustomerSubscriptionDeleted(object $subscription): Response
    {
        $existingSub = $this->subscriptionService->updateFromStripe(
            $this->objectToArray($subscription)
        );

        if (! $existingSub) {
            Log::warning('Stripe webhook: subscription not found for customer.subscription.deleted.', [
                'stripe_subscription_id' => $subscription->id,
            ]);

            return response('Subscription not found', 200);
        }

        $user = $existingSub->user;

        // Expire all active certificates when membership is canceled.
        $this->certificateService->expireCertificatesForUser($user);

        $user->notify(new SubscriptionCanceledNotification($existingSub));

        Log::info('Subscription deleted from webhook; certificates expired.', [
            'user_id' => $user->id,
            'stripe_subscription_id' => $subscription->id,
        ]);

        return response('Subscription deleted', 200);
    }

    /**
     * Handle invoice.paid - an invoice was successfully paid.
     */
    protected function handleInvoicePaid(object $invoice): Response
    {
        $user = $this->resolveUserFromCustomer($invoice->customer);

        if (! $user) {
            Log::warning('Stripe webhook: user not found for invoice.paid.', [
                'stripe_customer_id' => $invoice->customer,
                'stripe_invoice_id' => $invoice->id,
            ]);

            return response('User not found', 200);
        }

        $this->subscriptionService->createInvoiceFromStripe(
            $this->objectToArray($invoice),
            $user
        );

        // Sync certificate expiration dates after successful payment.
        $user->load('activeSubscription');
        if ($user->activeSubscription) {
            $this->certificateService->syncExpirationFromSubscription($user);
        }

        Log::info('Invoice paid recorded from webhook.', [
            'user_id' => $user->id,
            'stripe_invoice_id' => $invoice->id,
            'amount_paid' => $invoice->amount_paid,
        ]);

        return response('Invoice recorded', 200);
    }

    /**
     * Handle invoice.payment_failed - a payment attempt on an invoice failed.
     */
    protected function handleInvoicePaymentFailed(object $invoice): Response
    {
        $user = $this->resolveUserFromCustomer($invoice->customer);

        if (! $user) {
            Log::warning('Stripe webhook: user not found for invoice.payment_failed.', [
                'stripe_customer_id' => $invoice->customer,
                'stripe_invoice_id' => $invoice->id,
            ]);

            return response('User not found', 200);
        }

        // Record the failed invoice so it appears in the member's billing history.
        $this->subscriptionService->createInvoiceFromStripe(
            $this->objectToArray($invoice),
            $user
        );

        $localInvoice = Invoice::where('stripe_invoice_id', $invoice->id)->first();
        if ($localInvoice) {
            $user->notify(new PaymentFailedNotification($localInvoice));
        }

        Log::warning('Invoice payment failed.', [
            'user_id' => $user->id,
            'stripe_invoice_id' => $invoice->id,
            'amount_due' => $invoice->amount_due,
            'attempt_count' => $invoice->attempt_count ?? null,
            'next_payment_attempt' => $invoice->next_payment_attempt ?? null,
        ]);

        return response('Payment failure recorded', 200);
    }

    /**
     * Handle customer.updated - customer details changed in Stripe.
     */
    protected function handleCustomerUpdated(object $customer): Response
    {
        $user = User::where('stripe_customer_id', $customer->id)->first();

        if (! $user) {
            Log::warning('Stripe webhook: user not found for customer.updated.', [
                'stripe_customer_id' => $customer->id,
            ]);

            return response('User not found', 200);
        }

        // Sync email changes from Stripe back to local record if they differ.
        if (isset($customer->email) && $customer->email !== $user->email) {
            Log::info('Customer email updated via Stripe webhook.', [
                'user_id' => $user->id,
                'old_email' => $user->email,
                'new_email' => $customer->email,
            ]);

            $user->update(['email' => $customer->email]);
        }

        return response('Customer updated', 200);
    }

    /**
     * Handle checkout.session.completed - a Checkout Session was completed successfully.
     */
    protected function handleCheckoutSessionCompleted(object $session): Response
    {
        $userId = $session->metadata->user_id ?? null;
        $user = $userId ? User::find($userId) : null;

        if (! $user && isset($session->customer)) {
            $user = $this->resolveUserFromCustomer($session->customer);
        }

        if (! $user) {
            Log::warning('Stripe webhook: user not found for checkout.session.completed.', [
                'session_id' => $session->id,
                'metadata' => $session->metadata ?? null,
            ]);

            return response('User not found', 200);
        }

        // Ensure the Stripe customer ID is stored on the user.
        if (isset($session->customer) && $user->stripe_customer_id !== $session->customer) {
            $user->update(['stripe_customer_id' => $session->customer]);
        }

        // For subscription checkouts, set the payment method as the customer's default.
        if ($session->mode === 'subscription' && isset($session->subscription)) {
            try {
                $stripeSubscription = \Stripe\Subscription::retrieve([
                    'id' => $session->subscription,
                    'expand' => ['default_payment_method'],
                ]);
                $pmId = $stripeSubscription->default_payment_method->id
                    ?? $stripeSubscription->default_payment_method
                    ?? null;
                if ($pmId && $user->stripe_customer_id) {
                    \Stripe\Customer::update($user->stripe_customer_id, [
                        'invoice_settings' => ['default_payment_method' => $pmId],
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to set default payment method after checkout.', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

        }

        Log::info('Checkout session completed.', [
            'user_id' => $user->id,
            'session_id' => $session->id,
            'mode' => $session->mode ?? null,
        ]);

        return response('Checkout session handled', 200);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Resolve a local User by their Stripe customer ID.
     */
    protected function resolveUserFromCustomer(string $stripeCustomerId): ?User
    {
        return User::where('stripe_customer_id', $stripeCustomerId)->first();
    }

    /**
     * Convert a Stripe object to an array for service consumption.
     */
    protected function objectToArray(object $obj): array
    {
        return json_decode(json_encode($obj), true);
    }
}
