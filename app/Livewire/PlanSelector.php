<?php

namespace App\Livewire;

use App\Models\Plan;
use App\Services\StripeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PlanSelector extends Component
{
    public $plans;

    public ?Plan $currentPlan = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->plans = Plan::forUser($user)
            ->orderBy('sort_order')
            ->get();

        $activeSubscription = $user->activeSubscription;
        if ($activeSubscription) {
            $this->currentPlan = $activeSubscription->plan;
        }
    }

    public function selectPlan(int $planId): void
    {
        $plan = Plan::findOrFail($planId);
        $user = Auth::user();

        $stripeService = app(StripeService::class);

        $checkout = $stripeService->createSubscriptionCheckout(
            $user,
            $plan,
            route('membership.index') . '?checkout=success',
            route('membership.index') . '?checkout=canceled',
        );

        $this->redirect($checkout->url);
    }

    public function render()
    {
        return view('livewire.plan-selector');
    }
}
