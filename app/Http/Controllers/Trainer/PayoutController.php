<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Services\PayoutService;
use App\Services\StripeConnectService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function __construct(
        protected PayoutService $payoutService,
        protected StripeConnectService $stripeConnectService,
    ) {}

    /**
     * Show the payout dashboard with Stripe Connect status and recent earnings.
     */
    public function index(Request $request): View
    {
        $trainer = $request->user();
        $stripeAccount = $trainer->stripeAccount;

        // Sync Stripe account status if the trainer has connected
        if ($stripeAccount) {
            $this->stripeConnectService->syncAccountStatus($stripeAccount);
            $stripeAccount->refresh();
        }

        $earningsReport = $this->payoutService->getEarningsReport($trainer);

        return view('trainer.payouts.index', [
            'trainer' => $trainer,
            'stripeAccount' => $stripeAccount,
            'earningsReport' => $earningsReport,
        ]);
    }

    /**
     * Show Stripe Connect onboarding or initiate the connection flow.
     */
    public function connectStripe(Request $request): RedirectResponse
    {
        $trainer = $request->user();
        $stripeAccount = $trainer->stripeAccount;

        // Create a new Stripe Express account if the trainer doesn't have one yet
        if (!$stripeAccount) {
            $account = $this->stripeConnectService->createExpressAccount($trainer);
            $stripeAccount = $trainer->stripeAccount()->first();
        }

        // Generate the onboarding link and redirect the trainer to Stripe
        $onboardingLink = $this->stripeConnectService->createOnboardingLink(
            accountId: $stripeAccount->stripe_connect_account_id,
            refreshUrl: route('trainer.payouts.connect'),
            returnUrl: route('trainer.payouts.connect.callback'),
        );

        return redirect($onboardingLink->url);
    }

    /**
     * Handle the callback from Stripe Connect onboarding.
     */
    public function connectCallback(Request $request): RedirectResponse
    {
        $trainer = $request->user();
        $stripeAccount = $trainer->stripeAccount;

        if ($stripeAccount) {
            $this->stripeConnectService->syncAccountStatus($stripeAccount);
            $stripeAccount->refresh();

            if ($stripeAccount->isFullyOnboarded()) {
                return redirect()
                    ->route('trainer.payouts.index')
                    ->with('success', 'Stripe account connected successfully. You can now receive payouts.');
            }

            return redirect()
                ->route('trainer.payouts.index')
                ->with('warning', 'Stripe onboarding is not yet complete. Please finish setting up your account.');
        }

        return redirect()
            ->route('trainer.payouts.index')
            ->with('error', 'Unable to verify Stripe connection. Please try again.');
    }

    /**
     * Show earnings reports with date filtering.
     */
    public function reports(Request $request): View
    {
        $trainer = $request->user();

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : null;
        $to = isset($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : null;

        $earningsReport = $this->payoutService->getEarningsReport($trainer, $from, $to);

        return view('trainer.payouts.reports', [
            'trainer' => $trainer,
            'earningsReport' => $earningsReport,
            'filters' => [
                'from' => $validated['from'] ?? null,
                'to' => $validated['to'] ?? null,
            ],
        ]);
    }
}
