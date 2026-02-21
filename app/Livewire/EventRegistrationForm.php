<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\User;
use App\Services\EventPricingCalculator;
use Livewire\Component;

class EventRegistrationForm extends Component
{
    public Event $event;

    // Registrant fields
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';

    // Package selections (category_id => package_id)
    public array $selectedPackages = [];

    // Custom form responses (field_name => value)
    public array $formResponses = [];

    // Member verification
    public bool $isMemberVerified = false;
    public string $memberCheckEmail = '';
    public ?string $memberVerificationMessage = null;

    // Pricing
    public array $lineItems = [];
    public int $totalCents = 0;

    public function mount(Event $event): void
    {
        $this->event = $event;

        // Pre-fill from auth user
        if ($user = auth()->user()) {
            $this->first_name = $user->first_name ?? '';
            $this->last_name = $user->last_name ?? '';
            $this->email = $user->email ?? '';
            $this->phone = $user->phone ?? '';

            // Auto-verify member status (only if event has member pricing)
            if ($user->hasActiveSubscription() && $this->event->hasMemberPricing()) {
                $this->isMemberVerified = true;
                $this->memberVerificationMessage = 'Member pricing applied.';
            }
        }

        // Set defaults for form fields
        foreach ($this->event->formFields as $field) {
            if ($field->default_value) {
                $this->formResponses[$field->name] = $field->default_value;
            }
        }

        $this->recalculateTotal();
    }

    public function updatedSelectedPackages(): void
    {
        $this->recalculateTotal();
    }

    public function verifyMember(): void
    {
        $this->memberVerificationMessage = null;
        $this->isMemberVerified = false;

        if (empty($this->email)) {
            $this->memberVerificationMessage = 'Please enter your email address first.';
            return;
        }

        $user = User::where('email', $this->email)->first();

        if ($user && $user->hasActiveSubscription()) {
            $this->isMemberVerified = true;
            if ($this->event->hasMemberPricing()) {
                $this->memberVerificationMessage = 'Member verified! Member pricing has been applied.';
            } else {
                $this->memberVerificationMessage = 'Member verified!';
            }
        } else {
            $this->memberVerificationMessage = 'No active membership found for this email.';
        }

        $this->recalculateTotal();
    }

    public function recalculateTotal(): void
    {
        $packageIds = collect($this->selectedPackages)->filter()->values()->toArray();

        if (empty($packageIds)) {
            $this->lineItems = [];
            $this->totalCents = 0;
            return;
        }

        $calculator = app(EventPricingCalculator::class);
        $result = $calculator->calculate($packageIds, $this->isMemberVerified);

        $this->lineItems = $result['line_items'];
        $this->totalCents = $result['total_cents'];
    }

    public function submit(): mixed
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if (! $this->event->isRegistrationOpen()) {
            session()->flash('error', 'Registration is closed for this event.');
            return null;
        }

        // Validate required categories
        $packageIds = collect($this->selectedPackages)->filter()->values()->toArray();
        $calculator = app(EventPricingCalculator::class);
        $errors = $calculator->validateRequiredCategories($this->event, $packageIds);

        if (! empty($errors)) {
            foreach ($errors as $error) {
                $this->addError('packages', $error);
            }
            return null;
        }

        // Submit via standard form POST to the controller
        return $this->redirect(
            url()->current(),
            navigate: false
        );
    }

    public function render()
    {
        return view('livewire.event-registration-form');
    }
}
