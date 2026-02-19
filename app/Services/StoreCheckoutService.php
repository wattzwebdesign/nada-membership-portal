<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorOrderSplit;
use App\Notifications\CustomerSetPasswordNotification;
use App\Notifications\NewStoreOrderNotification;
use App\Notifications\StoreOrderConfirmationNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;
use Stripe\Transfer;

class StoreCheckoutService
{
    public function __construct(
        protected VendorPayoutService $vendorPayoutService,
    ) {}

    public function createCheckoutSession(Order $order): CheckoutSession
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = [];

        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $order->currency,
                    'unit_amount' => $item->unit_price_cents,
                    'product_data' => [
                        'name' => $item->product_title,
                    ],
                ],
                'quantity' => $item->quantity,
            ];

            // Add shipping as a separate line item if applicable
            if ($item->shipping_fee_cents > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $order->currency,
                        'unit_amount' => (int) ($item->shipping_fee_cents / $item->quantity),
                        'product_data' => [
                            'name' => "Shipping: {$item->product_title}",
                        ],
                    ],
                    'quantity' => $item->quantity,
                ];
            }
        }

        return CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('shop.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('shop.checkout.cancel'),
            'customer_email' => $order->customer_email,
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'type' => 'store_checkout',
            ],
        ]);
    }

    public function processPayment(Order $order, string $paymentIntentId): void
    {
        $order->update([
            'stripe_payment_intent_id' => $paymentIntentId,
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ]);

        // Link or create customer account
        $this->findOrCreateCustomerAccount($order);

        // Decrement stock
        foreach ($order->items as $item) {
            if ($item->product) {
                $item->product->decrementStock($item->quantity);
            }
        }

        // Create Stripe transfers to each vendor
        $this->createVendorTransfers($order);

        // Send confirmation email to customer
        try {
            Notification::route('mail', $order->customer_email)
                ->notify(new StoreOrderConfirmationNotification($order));
        } catch (\Throwable $e) {
            Log::error('Failed to send order confirmation', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        // Notify each vendor
        foreach ($order->vendorOrderSplits as $split) {
            try {
                $vendor = $split->vendorProfile->user;
                if ($vendor) {
                    $vendor->notify(new NewStoreOrderNotification($order, $split));
                }
            } catch (\Throwable $e) {
                Log::error('Failed to notify vendor of new order', [
                    'order_id' => $order->id,
                    'vendor_profile_id' => $split->vendor_profile_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function findOrCreateCustomerAccount(Order $order): void
    {
        // Already linked to a user (logged-in checkout)
        if ($order->user_id) {
            return;
        }

        $existingUser = User::where('email', $order->customer_email)->first();

        if ($existingUser) {
            $order->update(['user_id' => $existingUser->id]);

            return;
        }

        // Create new customer account
        try {
            $user = User::create([
                'first_name' => $order->customer_first_name,
                'last_name' => $order->customer_last_name,
                'email' => $order->customer_email,
                'password' => Str::random(32),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('customer');

            $order->update(['user_id' => $user->id]);

            $user->notify(new CustomerSetPasswordNotification($user));
        } catch (\Throwable $e) {
            Log::error('Failed to create customer account for order', [
                'order_id' => $order->id,
                'email' => $order->customer_email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function calculateVendorSplits(Order $order): void
    {
        $itemsByVendor = $order->items->groupBy('vendor_profile_id');

        foreach ($itemsByVendor as $vendorProfileId => $items) {
            $subtotal = $items->sum('total_cents');
            $vendorUserId = $items->first()->vendorProfile->user_id;

            $split = $this->vendorPayoutService->calculateSplit($subtotal, $vendorUserId);

            VendorOrderSplit::create([
                'order_id' => $order->id,
                'vendor_profile_id' => $vendorProfileId,
                'subtotal_cents' => $subtotal,
                'platform_percentage' => $split['platform_percentage'],
                'vendor_percentage' => $split['vendor_percentage'],
                'platform_fee_cents' => $split['platform_amount'],
                'vendor_payout_cents' => $split['vendor_amount'],
                'status' => 'pending',
            ]);
        }
    }

    protected function createVendorTransfers(Order $order): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        foreach ($order->vendorOrderSplits as $split) {
            $stripeAccount = $split->vendorProfile->user->stripeAccount ?? null;

            if (! $stripeAccount || ! $stripeAccount->isFullyOnboarded()) {
                Log::warning('Vendor does not have a connected Stripe account, skipping transfer.', [
                    'order_id' => $order->id,
                    'vendor_profile_id' => $split->vendor_profile_id,
                ]);
                continue;
            }

            try {
                $transfer = Transfer::create([
                    'amount' => $split->vendor_payout_cents,
                    'currency' => $order->currency,
                    'destination' => $stripeAccount->stripe_connect_account_id,
                    'transfer_group' => $order->order_number,
                    'metadata' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'vendor_profile_id' => $split->vendor_profile_id,
                    ],
                ]);

                $split->update([
                    'stripe_transfer_id' => $transfer->id,
                    'status' => 'transferred',
                ]);

                Log::info('Vendor transfer created.', [
                    'order_id' => $order->id,
                    'vendor_profile_id' => $split->vendor_profile_id,
                    'transfer_id' => $transfer->id,
                    'amount' => $split->vendor_payout_cents,
                ]);
            } catch (\Exception $e) {
                $split->update(['status' => 'failed']);

                Log::error('Vendor transfer failed.', [
                    'order_id' => $order->id,
                    'vendor_profile_id' => $split->vendor_profile_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
