<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\StripeService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Check for a pending plan from registration flow
        $pendingPlanId = $request->session()->pull('pending_plan_id');
        if ($pendingPlanId && !$user->hasActiveSubscription()) {
            $plan = Plan::where('is_active', true)->find($pendingPlanId);

            if ($plan) {
                // Auto-sync to Stripe if needed
                if (!$plan->stripe_price_id) {
                    try {
                        $this->stripeService->createStripeProductAndPrice($plan);
                        $plan->refresh();
                    } catch (\Stripe\Exception\ApiErrorException $e) {
                        return redirect()->route('membership.plans')
                            ->with('error', 'Unable to process this plan. Please select a plan below.');
                    }
                }

                $checkoutSession = $this->stripeService->createSubscriptionCheckout(
                    $user,
                    $plan,
                    route('membership.index') . '?checkout=success',
                    route('membership.plans') . '?checkout=canceled',
                );

                return redirect($checkoutSession->url);
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
