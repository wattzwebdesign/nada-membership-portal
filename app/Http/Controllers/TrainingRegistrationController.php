<?php

namespace App\Http\Controllers;

use App\Enums\RegistrationStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PayoutSetting;
use App\Models\Training;
use App\Models\TrainingRegistration;
use App\Models\User;
use App\Notifications\Concerns\SafelyNotifies;
use App\Notifications\TrainingRegisteredNotification;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

class TrainingRegistrationController extends Controller
{
    use SafelyNotifies;

    public function __construct(
        protected StripeService $stripeService,
    ) {}

    /**
     * List all training registrations for the authenticated user.
     */
    public function index(Request $request): View
    {
        $registrations = $request->user()
            ->trainingRegistrations()
            ->with(['training.trainer', 'invoice'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('trainings.my-registrations', compact('registrations'));
    }

    /**
     * Register the authenticated user for a training.
     */
    public function store(Request $request, Training $training): RedirectResponse
    {
        $user = $request->user();

        // Require active membership plan
        if (! $user->hasActiveSubscription()) {
            return back()->with('error', 'You need an active membership plan to register for trainings. Please subscribe first.');
        }

        // Check if already registered
        $existing = TrainingRegistration::where('training_id', $training->id)
            ->where('user_id', $user->id)
            ->where('status', '!=', RegistrationStatus::Canceled->value)
            ->first();

        if ($existing) {
            return back()->with('error', 'You are already registered for this training.');
        }

        // Check if the training is full
        if ($training->isFull()) {
            return back()->with('error', 'This training is full. No more spots are available.');
        }

        // Check if the training is still open for registration
        if ($training->start_date->isPast()) {
            return back()->with('error', 'Registration for this training has closed.');
        }

        // Paid training: redirect to Stripe Checkout
        if ($training->is_paid) {
            return $this->createPaidCheckout($user, $training);
        }

        // Free training: register directly
        $registration = TrainingRegistration::create([
            'training_id' => $training->id,
            'user_id' => $user->id,
            'status' => RegistrationStatus::Registered->value,
            'amount_paid_cents' => 0,
        ]);

        $this->safeNotify($user, new TrainingRegisteredNotification($registration));

        return redirect()->route('trainings.my-registrations')
            ->with('success', 'You have been registered for "' . $training->title . '".');
    }

    /**
     * Handle return from successful Stripe Checkout for training payment.
     */
    public function paymentSuccess(Request $request, Training $training): RedirectResponse
    {
        $sessionId = $request->query('session_id');
        $user = $request->user();

        if (! $sessionId) {
            return redirect()->route('trainings.show', $training)
                ->with('error', 'Payment session not found.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = CheckoutSession::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('trainings.show', $training)
                    ->with('error', 'Payment was not completed. Please try again.');
            }

            // Check for existing registration (idempotency — webhook may have created it)
            $existing = TrainingRegistration::where('training_id', $training->id)
                ->where('user_id', $user->id)
                ->where('status', '!=', RegistrationStatus::Canceled->value)
                ->first();

            if ($existing) {
                return redirect()->route('trainings.my-registrations')
                    ->with('success', 'You are registered for "' . $training->title . '".');
            }

            $amountDollars = $session->amount_total / 100;

            $invoice = Invoice::create([
                'user_id' => $user->id,
                'stripe_invoice_id' => $session->payment_intent,
                'status' => 'paid',
                'amount_due' => $amountDollars,
                'amount_paid' => $amountDollars,
                'currency' => $session->currency ?? 'usd',
                'paid_at' => now(),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Training registration: ' . $training->title . ' — ' . $training->start_date->format('M j, Y'),
                'quantity' => 1,
                'unit_price' => $amountDollars,
                'total' => $amountDollars,
            ]);

            $registration = TrainingRegistration::create([
                'training_id' => $training->id,
                'user_id' => $user->id,
                'status' => RegistrationStatus::Registered->value,
                'stripe_payment_intent_id' => $session->payment_intent,
                'amount_paid_cents' => $session->amount_total,
                'invoice_id' => $invoice->id,
            ]);

            $this->safeNotify($user, new TrainingRegisteredNotification($registration));

            return redirect()->route('trainings.my-registrations')
                ->with('success', 'Payment confirmed! You are registered for "' . $training->title . '".');
        } catch (\Exception $e) {
            Log::error('Training payment verification failed', [
                'user_id' => $user->id,
                'training_id' => $training->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('trainings.show', $training)
                ->with('error', 'We could not verify your payment. Please contact support if you were charged.');
        }
    }

    /**
     * Cancel a training registration.
     */
    public function destroy(Request $request, Training $training): RedirectResponse
    {
        $registration = TrainingRegistration::where('training_id', $training->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Only allow cancellation if still in 'registered' status and training hasn't started
        if ($registration->status !== RegistrationStatus::Registered) {
            return back()->with('error', 'This registration can no longer be canceled.');
        }

        if ($training->start_date->isPast()) {
            return back()->with('error', 'Cannot cancel registration for a training that has already started.');
        }

        $registration->update([
            'status' => RegistrationStatus::Canceled->value,
        ]);

        return redirect()->route('trainings.my-registrations')
            ->with('success', 'Your registration has been canceled.');
    }

    /**
     * Create a Stripe Checkout Session for a paid training and redirect.
     */
    protected function createPaidCheckout($user, Training $training): RedirectResponse
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $customer = $this->stripeService->getOrCreateCustomer($user);

            $trainer = $training->trainer;
            $stripeAccount = $trainer->stripeAccount;

            $checkoutParams = [
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $training->currency ?? 'usd',
                        'unit_amount' => $training->price_cents,
                        'product_data' => [
                            'name' => $training->title,
                            'description' => 'Training registration — ' . $training->start_date->format('M j, Y'),
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('trainings.payment.success', $training) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('trainings.show', $training),
                'metadata' => [
                    'user_id' => $user->id,
                    'training_id' => $training->id,
                    'type' => 'training_registration',
                ],
            ];

            // Route payment through trainer's connected Stripe account if available
            if ($stripeAccount && $stripeAccount->isFullyOnboarded()) {
                $payoutSettings = PayoutSetting::getForTrainer($trainer->id);
                $applicationFee = (int) round($training->price_cents * ($payoutSettings->platform_percentage / 100));

                $checkoutParams['payment_intent_data'] = [
                    'application_fee_amount' => $applicationFee,
                    'transfer_data' => [
                        'destination' => $stripeAccount->stripe_connect_account_id,
                    ],
                ];
            }

            $session = CheckoutSession::create($checkoutParams);

            return redirect($session->url);
        } catch (\Exception $e) {
            Log::error('Failed to create training checkout session', [
                'user_id' => $user->id,
                'training_id' => $training->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to start payment. Please try again or contact support.');
        }
    }
}
