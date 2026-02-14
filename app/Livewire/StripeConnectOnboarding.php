<?php

namespace App\Livewire;

use App\Models\StripeAccount;
use App\Services\StripeConnectService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class StripeConnectOnboarding extends Component
{
    public ?StripeAccount $stripeAccount = null;

    public bool $isConnected = false;

    public function mount(): void
    {
        $this->loadAccount();
    }

    public function loadAccount(): void
    {
        $user = Auth::user();
        $this->stripeAccount = $user->stripeAccount;

        if ($this->stripeAccount) {
            // Sync the latest status from Stripe
            try {
                $connectService = app(StripeConnectService::class);
                $connectService->syncAccountStatus($this->stripeAccount);
                $this->stripeAccount->refresh();
            } catch (\Exception $e) {
                // Continue with cached data
            }

            $this->isConnected = $this->stripeAccount->isFullyOnboarded();
        }
    }

    public function startOnboarding(): void
    {
        $user = Auth::user();
        $connectService = app(StripeConnectService::class);

        try {
            if (! $this->stripeAccount) {
                $account = $connectService->createExpressAccount($user);
                $this->stripeAccount = $user->stripeAccount()->first();
            }

            $accountLink = $connectService->createOnboardingLink(
                $this->stripeAccount->stripe_connect_account_id,
                route('trainer.stripe.refresh'),
                route('trainer.stripe.return'),
            );

            $this->redirect($accountLink->url);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to start Stripe onboarding. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.stripe-connect-onboarding');
    }
}
