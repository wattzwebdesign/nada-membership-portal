<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\PaymentMethod;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;
use Stripe\Subscription;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // Customer Management

    public function createCustomer(User $user): Customer
    {
        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->full_name,
            'metadata' => ['user_id' => $user->id],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    public function getOrCreateCustomer(User $user): Customer
    {
        if ($user->stripe_customer_id) {
            return Customer::retrieve($user->stripe_customer_id);
        }

        return $this->createCustomer($user);
    }

    public function updateCustomer(User $user): Customer
    {
        return Customer::update($user->stripe_customer_id, [
            'email' => $user->email,
            'name' => $user->full_name,
        ]);
    }

    // Checkout Sessions

    public function createSubscriptionCheckout(User $user, Plan $plan, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $customer = $this->getOrCreateCustomer($user);

        return CheckoutSession::create([
            'customer' => $customer->id,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ],
        ]);
    }

    // Subscription Management

    public function getSubscription(string $subscriptionId): Subscription
    {
        return Subscription::retrieve($subscriptionId);
    }

    public function switchPlan(string $subscriptionId, Plan $newPlan): Subscription
    {
        $subscription = Subscription::retrieve($subscriptionId);

        return Subscription::update($subscriptionId, [
            'items' => [[
                'id' => $subscription->items->data[0]->id,
                'price' => $newPlan->stripe_price_id,
            ]],
            'proration_behavior' => 'create_prorations',
        ]);
    }

    public function cancelAtPeriodEnd(string $subscriptionId): Subscription
    {
        return Subscription::update($subscriptionId, [
            'cancel_at_period_end' => true,
        ]);
    }

    public function cancelImmediately(string $subscriptionId): Subscription
    {
        $subscription = Subscription::retrieve($subscriptionId);
        $subscription->cancel();
        return $subscription;
    }

    public function reactivateSubscription(string $subscriptionId): Subscription
    {
        return Subscription::update($subscriptionId, [
            'cancel_at_period_end' => false,
        ]);
    }

    // Payment Methods

    public function attachPaymentMethod(string $customerId, string $paymentMethodId): PaymentMethod
    {
        $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->attach(['customer' => $customerId]);

        Customer::update($customerId, [
            'invoice_settings' => ['default_payment_method' => $paymentMethodId],
        ]);

        return $paymentMethod;
    }

    // Invoices

    public function listInvoices(string $customerId, int $limit = 10): array
    {
        $invoices = Invoice::all([
            'customer' => $customerId,
            'limit' => $limit,
        ]);

        return $invoices->data;
    }

    // Product & Price Sync

    public function createStripeProductAndPrice(Plan $plan): void
    {
        $product = Product::create([
            'name' => $plan->name,
            'description' => $plan->description,
            'metadata' => ['plan_id' => $plan->id],
        ]);

        $price = Price::create([
            'product' => $product->id,
            'unit_amount' => $plan->price_cents,
            'currency' => $plan->currency,
            'recurring' => [
                'interval' => $plan->billing_interval,
                'interval_count' => $plan->billing_interval_count,
            ],
            'metadata' => ['plan_id' => $plan->id],
        ]);

        $plan->update([
            'stripe_product_id' => $product->id,
            'stripe_price_id' => $price->id,
        ]);
    }

    public function updateStripeProduct(Plan $plan): void
    {
        Product::update($plan->stripe_product_id, [
            'name' => $plan->name,
            'description' => $plan->description ?? '',
        ]);
    }

    public function replaceStripePrice(Plan $plan): void
    {
        // Archive the old price (Stripe prices are immutable)
        Price::update($plan->stripe_price_id, ['active' => false]);

        $price = Price::create([
            'product' => $plan->stripe_product_id,
            'unit_amount' => $plan->price_cents,
            'currency' => $plan->currency,
            'recurring' => [
                'interval' => $plan->billing_interval,
                'interval_count' => $plan->billing_interval_count,
            ],
            'metadata' => ['plan_id' => $plan->id],
        ]);

        $plan->update(['stripe_price_id' => $price->id]);
    }

    // Products & Prices (for migration)

    public function listProducts(array $params = []): array
    {
        return Product::all(array_merge(['limit' => 100], $params))->data;
    }

    public function listPrices(string $productId): array
    {
        return Price::all(['product' => $productId, 'limit' => 100])->data;
    }

    public function listAllSubscriptions(array $params = []): array
    {
        return Subscription::all(array_merge(['limit' => 100], $params))->data;
    }

    public function listAllCustomers(array $params = []): array
    {
        return Customer::all(array_merge(['limit' => 100], $params))->data;
    }
}
