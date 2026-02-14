<?php

namespace App\Livewire;

use App\Enums\RegistrationStatus;
use App\Models\Training;
use App\Models\TrainingRegistration as TrainingRegistrationModel;
use App\Services\StripeConnectService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TrainingRegistration extends Component
{
    public Training $training;

    public bool $isRegistered = false;

    public ?TrainingRegistrationModel $registration = null;

    public function mount(Training $training): void
    {
        $this->training = $training->load('trainer');
        $this->checkRegistration();
    }

    public function checkRegistration(): void
    {
        $this->registration = TrainingRegistrationModel::where('training_id', $this->training->id)
            ->where('user_id', Auth::id())
            ->whereNot('status', RegistrationStatus::Canceled)
            ->first();

        $this->isRegistered = $this->registration !== null;
    }

    public function register(): void
    {
        $user = Auth::user();

        if ($this->isRegistered) {
            session()->flash('error', 'You are already registered for this training.');
            return;
        }

        if ($this->training->isFull()) {
            session()->flash('error', 'This training is full.');
            return;
        }

        // For paid trainings, redirect to Stripe
        if ($this->training->is_paid && $this->training->price_cents > 0) {
            $trainer = $this->training->trainer;
            $stripeAccount = $trainer->stripeAccount;

            if (! $stripeAccount || ! $stripeAccount->isFullyOnboarded()) {
                session()->flash('error', 'This training is not ready for registration. Please contact the trainer.');
                return;
            }

            $connectService = app(StripeConnectService::class);

            $paymentIntent = $connectService->createPaymentWithSplit(
                $this->training->price_cents,
                $this->training->currency ?? 'usd',
                $stripeAccount->stripe_connect_account_id,
                $trainer->id,
                [
                    'training_id' => $this->training->id,
                    'user_id' => $user->id,
                ]
            );

            // Create pending registration
            TrainingRegistrationModel::create([
                'training_id' => $this->training->id,
                'user_id' => $user->id,
                'status' => RegistrationStatus::Registered,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'amount_paid_cents' => $this->training->price_cents,
            ]);

            $this->checkRegistration();
            session()->flash('success', 'You have been registered for this training. Payment is being processed.');
            return;
        }

        // Free training - register directly
        TrainingRegistrationModel::create([
            'training_id' => $this->training->id,
            'user_id' => $user->id,
            'status' => RegistrationStatus::Registered,
            'amount_paid_cents' => 0,
        ]);

        $this->checkRegistration();
        session()->flash('success', 'You have been successfully registered for this training.');
    }

    public function cancelRegistration(): void
    {
        if (! $this->registration) {
            return;
        }

        $this->registration->update([
            'status' => RegistrationStatus::Canceled,
        ]);

        $this->checkRegistration();
        session()->flash('success', 'Your registration has been canceled.');
    }

    public function render()
    {
        return view('livewire.training-registration');
    }
}
