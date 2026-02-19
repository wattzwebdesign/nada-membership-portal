<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Customer-only users get a simplified dashboard
        if ($user->isCustomerOnly()) {
            $recentOrders = $user->shopOrders()
                ->where('status', '!=', 'pending')
                ->with('items')
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard-customer', compact('user', 'recentOrders'));
        }

        // Check for a pending plan from registration flow
        $pendingPlanId = Cache::pull("pending_plan:{$user->id}");
        if ($pendingPlanId && !$user->hasActiveSubscription()) {
            $plan = Plan::where('is_active', true)->find($pendingPlanId);

            if ($plan) {
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
                        );

                        return redirect($checkoutSession->url);
                    }

                    return redirect()->route('membership.plans')
                        ->with('error', 'Unable to process this plan. Please select a plan below.');
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    return redirect()->route('membership.plans')
                        ->with('error', 'Unable to process this plan. Please select a plan below.');
                }
            }

            return redirect()->route('membership.plans')
                ->with('error', 'The selected plan is no longer available. Please choose a plan below.');
        }

        $subscription = $user->activeSubscription;
        $certificates = $user->certificates()->where('status', 'active')->get();
        $upcomingTrainings = $user->trainingRegistrations()
            ->with('training')
            ->whereHas('training', fn($q) => $q->where('start_date', '>', now()))
            ->get();

        return view('dashboard', compact('user', 'subscription', 'certificates', 'upcomingTrainings'));
    }
}
