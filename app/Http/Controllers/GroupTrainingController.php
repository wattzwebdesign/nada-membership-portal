<?php

namespace App\Http\Controllers;

use App\Models\GroupTrainingRequest;
use App\Models\PayoutSetting;
use App\Models\User;
use App\Notifications\Concerns\SafelyNotifies;
use App\Services\GroupTrainingFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

class GroupTrainingController extends Controller
{
    use SafelyNotifies;

    public function __construct(
        protected GroupTrainingFeeService $feeService,
    ) {}

    public function create(Request $request): View
    {
        $trainers = User::trainersPublic()
            ->whereHas('stripeAccount', fn ($q) => $q->where('onboarding_complete', true)
                ->where('charges_enabled', true)
                ->where('payouts_enabled', true))
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('group-training.create', [
            'trainers' => $trainers,
            'feeType' => $this->feeService->getFeeType(),
            'feeValue' => $this->feeService->getFeeValue(),
            'prefillTrainer' => $request->query('trainer'),
            'prefillPrice' => $request->query('price'),
            'prefillTickets' => $request->query('tickets'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trainer_id' => 'required|exists:users,id',
            'company_first_name' => 'required|string|max:255',
            'company_last_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'training_name' => 'required|string|max:255',
            'training_date' => 'required|date|after:today',
            'training_city' => 'required|string|max:255',
            'training_state' => 'required|string|size:2',
            'cost_per_ticket_cents' => 'required|integer|min:100',
            'number_of_tickets' => 'required|integer|min:1|max:500',
            'members' => 'required|array|min:1',
            'members.*.first_name' => 'required|string|max:255',
            'members.*.last_name' => 'required|string|max:255',
            'members.*.email' => 'required|email|max:255',
        ]);

        // Verify member count matches ticket count
        if (count($validated['members']) !== (int) $validated['number_of_tickets']) {
            return back()->withInput()->withErrors([
                'members' => 'The number of members must match the number of tickets.',
            ]);
        }

        // Verify trainer has connected Stripe account
        $trainer = User::find($validated['trainer_id']);
        if (! $trainer || ! $trainer->hasConnectedStripeAccount()) {
            return back()->withInput()->withErrors([
                'trainer_id' => 'The selected trainer cannot accept payments at this time.',
            ]);
        }

        $subtotalCents = $validated['cost_per_ticket_cents'] * $validated['number_of_tickets'];
        $feeCents = $this->feeService->calculateFeeCents($subtotalCents);
        $totalCents = $subtotalCents + $feeCents;

        try {
            $groupRequest = DB::transaction(function () use ($validated, $feeCents, $totalCents) {
                $groupRequest = GroupTrainingRequest::create([
                    'trainer_id' => $validated['trainer_id'],
                    'company_first_name' => $validated['company_first_name'],
                    'company_last_name' => $validated['company_last_name'],
                    'company_email' => $validated['company_email'],
                    'training_name' => $validated['training_name'],
                    'training_date' => $validated['training_date'],
                    'training_city' => $validated['training_city'],
                    'training_state' => $validated['training_state'],
                    'cost_per_ticket_cents' => $validated['cost_per_ticket_cents'],
                    'number_of_tickets' => $validated['number_of_tickets'],
                    'transaction_fee_cents' => $feeCents,
                    'total_amount_cents' => $totalCents,
                    'status' => 'pending_payment',
                ]);

                foreach ($validated['members'] as $member) {
                    $groupRequest->members()->create([
                        'first_name' => $member['first_name'],
                        'last_name' => $member['last_name'],
                        'email' => $member['email'],
                    ]);
                }

                return $groupRequest;
            });

            return $this->createCheckoutSession($groupRequest, $trainer);
        } catch (\Exception $e) {
            Log::error('Failed to create group training request', [
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Unable to process your request. Please try again.');
        }
    }

    public function success(Request $request): View
    {
        $sessionId = $request->query('session_id');
        $groupRequest = null;

        if ($sessionId) {
            $groupRequest = GroupTrainingRequest::where('stripe_checkout_session_id', $sessionId)
                ->with(['trainer', 'members'])
                ->first();
        }

        return view('group-training.success', [
            'groupRequest' => $groupRequest,
        ]);
    }

    public function cancel(Request $request): View
    {
        return view('group-training.cancel');
    }

    protected function createCheckoutSession(GroupTrainingRequest $groupRequest, User $trainer): RedirectResponse
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $stripeAccount = $trainer->stripeAccount;

        $lineItems = [
            [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $groupRequest->cost_per_ticket_cents,
                    'product_data' => [
                        'name' => $groupRequest->training_name,
                        'description' => 'Group training ticket — ' . $groupRequest->training_date->format('M j, Y') . ' — ' . $groupRequest->training_city . ', ' . $groupRequest->training_state,
                    ],
                ],
                'quantity' => $groupRequest->number_of_tickets,
            ],
        ];

        if ($groupRequest->transaction_fee_cents > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $groupRequest->transaction_fee_cents,
                    'product_data' => [
                        'name' => 'Transaction Fee',
                        'description' => $this->feeService->getFeeDescription(),
                    ],
                ],
                'quantity' => 1,
            ];
        }

        // Platform keeps the fee; trainer gets subtotal minus platform %
        $payoutSettings = PayoutSetting::getForTrainer($trainer->id);
        $subtotalCents = $groupRequest->subtotal_cents;
        $platformPercentageFee = (int) round($subtotalCents * ($payoutSettings->platform_percentage / 100));
        $applicationFeeAmount = $platformPercentageFee + $groupRequest->transaction_fee_cents;

        $checkoutParams = [
            'customer_email' => $groupRequest->company_email,
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('group-training.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('group-training.cancel'),
            'metadata' => [
                'type' => 'group_training',
                'group_training_request_id' => $groupRequest->id,
                'trainer_id' => $trainer->id,
            ],
            'payment_intent_data' => [
                'application_fee_amount' => $applicationFeeAmount,
                'transfer_data' => [
                    'destination' => $stripeAccount->stripe_connect_account_id,
                ],
            ],
        ];

        $session = CheckoutSession::create($checkoutParams);

        $groupRequest->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        return redirect($session->url);
    }
}
