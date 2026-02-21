<?php

namespace App\Services;

use App\Enums\EventPaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Stripe;

class EventRegistrationService
{
    public function __construct(
        protected EventPricingCalculator $pricingCalculator,
    ) {}

    /**
     * Register for an event.
     */
    public function register(Event $event, array $data, ?User $user = null): EventRegistration
    {
        return DB::transaction(function () use ($event, $data, $user) {
            $isMember = $user && $user->hasActiveSubscription();
            $selectedPackageIds = $data['selected_packages'] ?? [];

            $pricing = $this->pricingCalculator->calculate($selectedPackageIds, $isMember);

            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $user?->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => RegistrationStatus::Registered,
                'payment_status' => $pricing['total_cents'] === 0
                    ? EventPaymentStatus::Comped
                    : EventPaymentStatus::Unpaid,
                'total_amount_cents' => $pricing['total_cents'],
                'is_member_pricing' => $isMember,
                'form_data' => $data['form_responses'] ?? null,
            ]);

            // Attach selected packages with pricing details
            foreach ($pricing['line_items'] as $item) {
                $registration->pricingPackages()->attach($item['package_id'], [
                    'pricing_category_id' => $item['category_id'],
                    'unit_price_cents' => $item['price_cents'],
                    'is_member_pricing' => $item['is_member_pricing'],
                    'is_early_bird' => $item['is_early_bird'],
                ]);
            }

            // Increment quantity_sold on packages
            foreach ($selectedPackageIds as $packageId) {
                \App\Models\EventPricingPackage::where('id', $packageId)->increment('quantity_sold');
            }

            return $registration;
        });
    }

    /**
     * Create a Stripe Checkout session for a paid registration.
     */
    public function createCheckoutSession(EventRegistration $registration): string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $event = $registration->event;
        $lineItems = [];

        foreach ($registration->pricingPackages as $package) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $package->category->name . ': ' . $package->name,
                        'description' => $event->title,
                    ],
                    'unit_amount' => $package->pivot->unit_price_cents,
                ],
                'quantity' => 1,
            ];
        }

        $session = StripeCheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('events.payment.success', [
                'event' => $event->slug,
            ]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('public.events.show', $event->slug),
            'customer_email' => $registration->email,
            'metadata' => [
                'type' => 'event_registration',
                'event_registration_id' => $registration->id,
                'event_id' => $event->id,
            ],
        ]);

        $registration->update(['stripe_checkout_session_id' => $session->id]);

        return $session->url;
    }

    /**
     * Process payment after successful Stripe Checkout.
     */
    public function processPayment(EventRegistration $registration, string $paymentIntentId): void
    {
        DB::transaction(function () use ($registration, $paymentIntentId) {
            $registration->update([
                'payment_status' => EventPaymentStatus::Paid,
                'stripe_payment_intent_id' => $paymentIntentId,
            ]);

            // Create invoice
            $invoice = Invoice::create([
                'user_id' => $registration->user_id,
                'status' => 'paid',
                'amount_due' => $registration->total_amount_cents / 100,
                'amount_paid' => $registration->total_amount_cents / 100,
                'currency' => 'usd',
                'paid_at' => now(),
            ]);

            foreach ($registration->pricingPackages as $package) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $registration->event->title . ' - ' . $package->name,
                    'quantity' => 1,
                    'unit_price' => $package->pivot->unit_price_cents / 100,
                    'total' => $package->pivot->unit_price_cents / 100,
                ]);
            }

            $registration->update(['invoice_id' => $invoice->id]);
        });
    }

    /**
     * Check in an attendee by QR code token.
     */
    public function checkInByToken(string $token, User $admin): ?EventRegistration
    {
        $registration = EventRegistration::where('qr_code_token', $token)
            ->whereNull('checked_in_at')
            ->first();

        if (! $registration) {
            return null;
        }

        $registration->update([
            'checked_in_at' => now(),
            'checked_in_by' => $admin->id,
            'status' => RegistrationStatus::Attended,
        ]);

        return $registration;
    }

    /**
     * Cancel a registration.
     */
    public function cancelRegistration(EventRegistration $registration): void
    {
        $registration->update([
            'status' => RegistrationStatus::Canceled,
            'canceled_at' => now(),
        ]);

        // Decrement quantity_sold on packages
        foreach ($registration->pricingPackages as $package) {
            \App\Models\EventPricingPackage::where('id', $package->id)
                ->where('quantity_sold', '>', 0)
                ->decrement('quantity_sold');
        }

        // Void wallet passes
        try {
            app(WalletPassService::class)->voidEventPasses($registration);
        } catch (\Exception $e) {
            Log::error('Failed to void event wallet passes.', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate QR code as base64 PNG.
     */
    public function generateQrCode(EventRegistration $registration): string
    {
        $checkInUrl = route('filament.admin.pages.event-check-in') . '?scan=' . $registration->qr_code_token;

        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($checkInUrl);

        return base64_encode($qrCode);
    }
}
