<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\GroupTrainingRequest;
use App\Models\Invoice;
use App\Models\Training;
use App\Models\TrainingRegistration;
use App\Models\User;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\GroupTrainingConfirmationNotification;
use App\Notifications\GroupTrainingPaidNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\SubscriptionCanceledNotification;
use App\Notifications\SubscriptionConfirmedNotification;
use App\Notifications\SubscriptionRenewedNotification;
use App\Notifications\NewTrainingRegistrationNotification;
use App\Notifications\TrainingRegisteredNotification;
use App\Services\CertificateService;
use App\Services\SubscriptionService;
use App\Services\TermsConsentService;
use App\Services\WalletPassService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    use SafelyNotifies;
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected CertificateService $certificateService,
    ) {}

    public function handle(Request $request): Response
    {
        $webhookSecret = config('services.stripe.webhook_secret');
        if (empty($webhookSecret)) {
            Log::critical('Stripe webhook secret is not configured.');

            return response('Webhook configuration error', 500);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
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
            // Ensure user has the member role (handles customer-to-member upgrade)
            if (!$user->hasRole('member')) {
                $user->assignRole('member');
            }

            $this->safeNotify($user, new SubscriptionConfirmedNotification($localSubscription));
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
        // Capture old status before the update so we can detect transitions
        $existingLocal = \App\Models\Subscription::where('stripe_subscription_id', $subscription->id)->first();
        $oldStatus = $existingLocal?->getRawOriginal('status');

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

        // Only send renewed notification when the subscription *transitions* to active
        // (not when it's already active and gets updated for other reasons like a card change).
        if ($subscription->status === 'active') {
            $user->load('activeSubscription');
            $this->certificateService->syncExpirationFromSubscription($user);

            if ($oldStatus !== 'active') {
                $this->safeNotify($user, new SubscriptionRenewedNotification($existingSub));
            }
        }

        app(WalletPassService::class)->updateAllPassesForUser($user);

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

        $this->safeNotify($user, new SubscriptionCanceledNotification($existingSub));

        app(WalletPassService::class)->updateAllPassesForUser($user);

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
            $this->safeNotify($user, new PaymentFailedNotification($localInvoice));
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
        $type = $session->metadata->type ?? null;

        // Handle group training payment (no authenticated user — public form)
        if ($type === 'group_training' && $session->payment_status === 'paid') {
            return $this->handleGroupTrainingPayment($session);
        }

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
            $user->stripe_customer_id = $session->customer;
            $user->save();
        }

        if ($type === 'training_registration' && $session->payment_status === 'paid') {
            $trainingId = $session->metadata->training_id ?? null;
            $training = $trainingId ? Training::find($trainingId) : null;

            if ($training) {
                // Check for any existing registration (including canceled)
                $existing = TrainingRegistration::where('training_id', $training->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($existing && $existing->status !== RegistrationStatus::Canceled) {
                    // Already registered (non-canceled) — skip
                    Log::info('Training registration already exists (webhook skipped).', [
                        'user_id' => $user->id,
                        'training_id' => $training->id,
                    ]);
                } elseif ($existing) {
                    // Reactivate canceled registration
                    $existing->update([
                        'status' => RegistrationStatus::Registered->value,
                        'stripe_payment_intent_id' => $session->payment_intent,
                        'amount_paid_cents' => $session->amount_total,
                    ]);

                    $this->safeNotify($user, new TrainingRegisteredNotification($existing));
                    $this->safeNotify($training->trainer, new NewTrainingRegistrationNotification($existing));

                    Log::info('Training registration reactivated from webhook.', [
                        'user_id' => $user->id,
                        'training_id' => $training->id,
                        'payment_intent' => $session->payment_intent,
                    ]);
                } else {
                    // No existing row — create new
                    try {
                        $registration = TrainingRegistration::create([
                            'training_id' => $training->id,
                            'user_id' => $user->id,
                            'status' => RegistrationStatus::Registered->value,
                            'stripe_payment_intent_id' => $session->payment_intent,
                            'amount_paid_cents' => $session->amount_total,
                        ]);

                        $this->safeNotify($user, new TrainingRegisteredNotification($registration));
                        $this->safeNotify($training->trainer, new NewTrainingRegistrationNotification($registration));

                        Log::info('Training registration created from webhook.', [
                            'user_id' => $user->id,
                            'training_id' => $training->id,
                            'payment_intent' => $session->payment_intent,
                        ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Race condition: redirect handler already created it
                        if (str_contains($e->getMessage(), 'UNIQUE constraint') || str_contains($e->getMessage(), 'Duplicate entry')) {
                            Log::info('Training registration race condition handled (webhook).', [
                                'user_id' => $user->id,
                                'training_id' => $training->id,
                            ]);
                        } else {
                            throw $e;
                        }
                    }
                }
            }
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

        // Handle event registration payment
        if ($type === 'event_registration' && $session->payment_status === 'paid') {
            $registrationId = $session->metadata->event_registration_id ?? null;
            $eventRegistration = $registrationId
                ? \App\Models\EventRegistration::find($registrationId)
                : null;

            if ($eventRegistration && $eventRegistration->payment_status->value === 'unpaid') {
                $registrationService = app(\App\Services\EventRegistrationService::class);
                $registrationService->processPayment($eventRegistration, $session->payment_intent);

                // Send confirmation notifications
                try {
                    \Illuminate\Support\Facades\Notification::route('mail', $eventRegistration->email)
                        ->notify(new \App\Notifications\EventRegistrationConfirmation($eventRegistration->fresh()));

                    $adminEmail = \App\Models\SiteSetting::adminEmail();
                    if ($adminEmail) {
                        \Illuminate\Support\Facades\Notification::route('mail', $adminEmail)
                            ->notify(new \App\Notifications\NewEventRegistrationNotification($eventRegistration));
                    }
                } catch (\Exception $notifyError) {
                    Log::error('Event registration webhook notification failed.', [
                        'error' => $notifyError->getMessage(),
                    ]);
                }

                Log::info('Event registration paid from webhook.', [
                    'registration_id' => $eventRegistration->id,
                    'payment_intent' => $session->payment_intent,
                ]);
            }
        }

        // Handle store checkout payment
        if ($type === 'store_checkout' && $session->payment_status === 'paid') {
            $orderId = $session->metadata->order_id ?? null;
            $order = $orderId ? \App\Models\Order::find($orderId) : null;

            if ($order && $order->status->value === 'pending') {
                app(\App\Services\StoreCheckoutService::class)->processPayment($order, $session->payment_intent);

                Log::info('Store order paid from webhook.', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_intent' => $session->payment_intent,
                ]);
            }
        }

        // Attach Stripe transaction ID to consent signature if present
        $tcSignatureId = $session->metadata->tc_signature_id ?? null;
        $paymentIntent = $session->payment_intent ?? $session->subscription ?? null;
        if ($tcSignatureId && $paymentIntent) {
            TermsConsentService::attachTransaction((int) $tcSignatureId, $paymentIntent);
        }

        Log::info('Checkout session completed.', [
            'user_id' => $user->id,
            'session_id' => $session->id,
            'mode' => $session->mode ?? null,
        ]);

        return response('Checkout session handled', 200);
    }

    /**
     * Handle a paid group training checkout session (public form, no auth user).
     */
    protected function handleGroupTrainingPayment(object $session): Response
    {
        $requestId = $session->metadata->group_training_request_id ?? null;
        $groupRequest = $requestId
            ? GroupTrainingRequest::find($requestId)
            : GroupTrainingRequest::where('stripe_checkout_session_id', $session->id)->first();

        if ($groupRequest && $groupRequest->status !== 'paid') {
            $groupRequest->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $session->payment_intent,
                'paid_at' => now(),
            ]);

            // Notify trainer
            $trainer = $groupRequest->trainer;
            if ($trainer) {
                $this->safeNotify($trainer, new GroupTrainingPaidNotification($groupRequest));
            }

            // Notify admin
            $adminEmail = \App\Models\SiteSetting::adminEmail();
            $this->safeNotifyRoute($adminEmail, new GroupTrainingPaidNotification($groupRequest));

            // Confirmation to company contact
            $this->safeNotifyRoute(
                $groupRequest->company_email,
                new GroupTrainingConfirmationNotification($groupRequest)
            );

            Log::info('Group training request paid from webhook.', [
                'group_training_request_id' => $groupRequest->id,
                'payment_intent' => $session->payment_intent,
            ]);
        }

        return response('Group training payment handled', 200);
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
