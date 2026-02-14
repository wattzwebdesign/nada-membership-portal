<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
    ) {}

    /**
     * Show billing overview and current payment method on file.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $paymentMethod = null;

        $subscription = $user->activeSubscription?->load('plan');

        if ($user->stripe_customer_id) {
            try {
                // Try customer's default payment method first
                $customer = \Stripe\Customer::retrieve([
                    'id' => $user->stripe_customer_id,
                    'expand' => ['invoice_settings.default_payment_method'],
                ]);
                $paymentMethod = $customer->invoice_settings->default_payment_method;

                // Fall back to the active subscription's default payment method
                if (!$paymentMethod && $subscription?->stripe_subscription_id) {
                    $stripeSubscription = \Stripe\Subscription::retrieve([
                        'id' => $subscription->stripe_subscription_id,
                        'expand' => ['default_payment_method'],
                    ]);
                    $paymentMethod = $stripeSubscription->default_payment_method;
                }

                // Last resort: get the most recent payment method attached to the customer
                if (!$paymentMethod) {
                    $paymentMethods = \Stripe\PaymentMethod::all([
                        'customer' => $user->stripe_customer_id,
                        'type' => 'card',
                        'limit' => 1,
                    ]);
                    $paymentMethod = $paymentMethods->data[0] ?? null;
                }
            } catch (\Stripe\Exception\ApiErrorException $e) {
                // Customer may not exist in Stripe; gracefully handle
            }
        }

        return view('membership.billing', compact('user', 'paymentMethod', 'subscription'));
    }

    /**
     * Update the user's default payment method in Stripe.
     */
    public function updatePaymentMethod(Request $request): RedirectResponse
    {
        $request->validate([
            'payment_method_id' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!$user->stripe_customer_id) {
            return back()->with('error', 'No Stripe customer record found. Please contact support.');
        }

        try {
            $this->stripeService->attachPaymentMethod(
                $user->stripe_customer_id,
                $request->input('payment_method_id'),
            );

            return redirect()->route('billing.index')
                ->with('success', 'Your payment method has been updated.');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return back()->with('error', 'Failed to update payment method: ' . $e->getMessage());
        }
    }
}
