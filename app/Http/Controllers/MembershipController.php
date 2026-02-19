<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Plan;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\SubscriptionCanceledNotification;
use App\Services\StripeService;
use App\Services\SubscriptionService;
use App\Services\TermsConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipController extends Controller
{
    use SafelyNotifies;

    public function __construct(
        protected StripeService $stripeService,
        protected SubscriptionService $subscriptionService,
        protected TermsConsentService $termsConsentService,
    ) {}

    /**
     * Show the user's current membership plan and status.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $subscription = $user->activeSubscription?->load('plan');

        return view('membership.index', compact('user', 'subscription'));
    }

    /**
     * Show all available plans the user is eligible to subscribe to.
     */
    public function plans(Request $request): View
    {
        $user = $request->user();
        $plans = Plan::forUser($user)->orderBy('sort_order')->get()
            ->sortByDesc(fn ($plan) => $plan->discount_required !== null)
            ->values();
        $currentSubscription = $user->activeSubscription?->load('plan');
        $currentPlan = $currentSubscription?->plan;
        $activeTerms = Agreement::getActiveTerms();

        return view('membership.plans', compact('plans', 'currentSubscription', 'currentPlan', 'user', 'activeTerms'));
    }

    /**
     * Create a Stripe Checkout session and redirect the user.
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'accept_terms' => ['required', 'accepted'],
        ]);

        $user = $request->user();
        $plan = Plan::findOrFail($request->input('plan_id'));

        // Prevent subscribing if the user already has an active subscription
        if ($user->hasActiveSubscription()) {
            return back()->with('error', 'You already have an active subscription. Please switch plans instead.');
        }

        // Record T&C consent
        $signature = $this->termsConsentService->recordConsent($request, $user, 'membership_subscription', Plan::class, $plan->id, $plan->price_cents);
        $tcMetadata = $this->termsConsentService->stripeMetadata($signature);

        try {
            // Auto-sync to Stripe if missing
            if (!$plan->stripe_price_id) {
                $this->stripeService->createStripeProductAndPrice($plan);
                $plan->refresh();
            }

            $checkoutSession = $this->stripeService->createSubscriptionCheckout(
                $user,
                $plan,
                route('membership.index') . '?checkout=success',
                route('membership.plans') . '?checkout=canceled',
                $tcMetadata,
            );

            return redirect($checkoutSession->url);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Stale Stripe price — re-sync and retry once
            if (str_contains($e->getMessage(), 'No such price')) {
                $this->stripeService->createStripeProductAndPrice($plan);
                $plan->refresh();

                $checkoutSession = $this->stripeService->createSubscriptionCheckout(
                    $user,
                    $plan,
                    route('membership.index') . '?checkout=success',
                    route('membership.plans') . '?checkout=canceled',
                    $tcMetadata,
                );

                return redirect($checkoutSession->url);
            }

            return back()->with('error', 'Unable to process this plan. Please contact support.');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return back()->with('error', 'Unable to process this plan. Please contact support.');
        }
    }

    /**
     * Switch the user's current subscription to a different plan (proration applied).
     */
    public function switchPlan(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'accept_terms' => ['required', 'accepted'],
        ]);

        $user = $request->user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return back()->with('error', 'You do not have an active subscription to switch.');
        }

        $newPlan = Plan::findOrFail($request->input('plan_id'));

        // Record T&C consent
        $signature = $this->termsConsentService->recordConsent($request, $user, 'plan_switch', Plan::class, $newPlan->id, $newPlan->price_cents);
        $tcMetadata = $this->termsConsentService->stripeMetadata($signature);

        $this->stripeService->switchPlan($subscription->stripe_subscription_id, $newPlan, $tcMetadata);

        $subscription->update([
            'plan_id' => $newPlan->id,
            'stripe_price_id' => $newPlan->stripe_price_id,
        ]);

        return redirect()->route('membership.index')
            ->with('success', 'Your plan has been switched to ' . $newPlan->name . '.');
    }

    /**
     * Cancel the user's subscription at the end of the current billing period.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription;

        if (!$subscription) {
            return back()->with('error', 'You do not have an active subscription to cancel.');
        }

        $this->stripeService->cancelAtPeriodEnd($subscription->stripe_subscription_id);

        $subscription->update([
            'cancel_at_period_end' => true,
        ]);

        $this->safeNotify($user, new SubscriptionCanceledNotification($subscription));

        return redirect()->route('membership.index')
            ->with('success', 'Your subscription has been canceled and will remain active until ' . $subscription->current_period_end->format('F j, Y') . '.');
    }

    /**
     * Reactivate a subscription that was set to cancel at period end.
     */
    public function reactivate(Request $request): RedirectResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription;

        if (!$subscription || !$subscription->cancel_at_period_end) {
            return back()->with('error', 'You do not have a subscription pending cancellation.');
        }

        $this->stripeService->reactivateSubscription($subscription->stripe_subscription_id);

        $subscription->update([
            'cancel_at_period_end' => false,
        ]);

        return redirect()->route('membership.index')
            ->with('success', 'Your subscription has been reactivated.');
    }
}
