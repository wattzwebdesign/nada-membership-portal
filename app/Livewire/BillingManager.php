<?php

namespace App\Livewire;

use App\Models\Subscription;
use App\Services\StripeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Stripe\Customer;
use Stripe\PaymentMethod;

#[Layout('layouts.app')]
class BillingManager extends Component
{
    public ?Subscription $subscription = null;

    public ?string $cardBrand = null;
    public ?string $cardLast4 = null;
    public ?string $cardExpiry = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->subscription = $user->activeSubscription;

        $this->loadPaymentMethod();
    }

    public function loadPaymentMethod(): void
    {
        $user = Auth::user();

        if (! $user->stripe_customer_id) {
            return;
        }

        try {
            $stripeService = app(StripeService::class);
            $customer = Customer::retrieve($user->stripe_customer_id);

            $defaultPaymentMethodId = $customer->invoice_settings->default_payment_method ?? null;

            if ($defaultPaymentMethodId) {
                $pm = PaymentMethod::retrieve($defaultPaymentMethodId);
                $this->cardBrand = ucfirst($pm->card->brand ?? 'Card');
                $this->cardLast4 = $pm->card->last4 ?? '****';
                $this->cardExpiry = ($pm->card->exp_month ?? '??') . '/' . ($pm->card->exp_year ?? '????');
            }
        } catch (\Exception $e) {
            // Silently fail - payment method info is not critical
        }
    }

    public function cancelSubscription(): void
    {
        if (! $this->subscription || ! $this->subscription->stripe_subscription_id) {
            session()->flash('error', 'No active subscription found.');
            return;
        }

        try {
            $stripeService = app(StripeService::class);
            $stripeService->cancelAtPeriodEnd($this->subscription->stripe_subscription_id);

            $this->subscription->update([
                'cancel_at_period_end' => true,
                'canceled_at' => now(),
            ]);

            $this->subscription->refresh();

            session()->flash('success', 'Your subscription will be canceled at the end of the current billing period.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to cancel subscription. Please try again or contact support.');
        }
    }

    public function reactivateSubscription(): void
    {
        if (! $this->subscription || ! $this->subscription->stripe_subscription_id) {
            session()->flash('error', 'No subscription found to reactivate.');
            return;
        }

        try {
            $stripeService = app(StripeService::class);
            $stripeService->reactivateSubscription($this->subscription->stripe_subscription_id);

            $this->subscription->update([
                'cancel_at_period_end' => false,
                'canceled_at' => null,
            ]);

            $this->subscription->refresh();

            session()->flash('success', 'Your subscription has been reactivated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reactivate subscription. Please try again or contact support.');
        }
    }

    public function render()
    {
        return view('livewire.billing-manager');
    }
}
