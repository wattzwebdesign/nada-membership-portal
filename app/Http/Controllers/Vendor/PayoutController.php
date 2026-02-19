<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\StripeConnectService;
use App\Services\VendorPayoutService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function __construct(
        protected VendorPayoutService $vendorPayoutService,
        protected StripeConnectService $stripeConnectService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $stripeAccount = $user->stripeAccount;

        if ($stripeAccount) {
            $this->stripeConnectService->syncAccountStatus($stripeAccount);
            $stripeAccount->refresh();
        }

        $earningsReport = $this->vendorPayoutService->getEarningsReport($user);

        return view('store-vendor.payouts.index', [
            'vendor' => $user,
            'stripeAccount' => $stripeAccount,
            'earningsReport' => $earningsReport,
        ]);
    }

    public function connectStripe(Request $request): RedirectResponse
    {
        $user = $request->user();
        $stripeAccount = $user->stripeAccount;

        if ($stripeAccount && $stripeAccount->isFullyOnboarded()) {
            try {
                $loginLink = $this->stripeConnectService->createLoginLink(
                    $stripeAccount->stripe_connect_account_id
                );

                return redirect($loginLink->url);
            } catch (\Exception $e) {
                // Fall through to onboarding link
            }
        }

        if (!$stripeAccount) {
            $this->stripeConnectService->createExpressAccount($user);
            $stripeAccount = $user->stripeAccount()->first();
        }

        $onboardingLink = $this->stripeConnectService->createOnboardingLink(
            accountId: $stripeAccount->stripe_connect_account_id,
            refreshUrl: route('vendor.payouts.connect'),
            returnUrl: route('vendor.payouts.connect.callback'),
        );

        return redirect($onboardingLink->url);
    }

    public function connectCallback(Request $request): RedirectResponse
    {
        $user = $request->user();
        $stripeAccount = $user->stripeAccount;

        if ($stripeAccount) {
            $this->stripeConnectService->syncAccountStatus($stripeAccount);
            $stripeAccount->refresh();

            if ($stripeAccount->isFullyOnboarded()) {
                return redirect()
                    ->route('vendor.payouts.index')
                    ->with('success', 'Stripe account connected successfully. You can now receive payouts.');
            }

            return redirect()
                ->route('vendor.payouts.index')
                ->with('warning', 'Stripe onboarding is not yet complete. Please finish setting up your account.');
        }

        return redirect()
            ->route('vendor.payouts.index')
            ->with('error', 'Unable to verify Stripe connection. Please try again.');
    }

    public function reports(Request $request): View
    {
        $user = $request->user();

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : null;
        $to = isset($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : null;

        $earningsReport = $this->vendorPayoutService->getEarningsReport($user, $from, $to);

        return view('store-vendor.payouts.reports', [
            'vendor' => $user,
            'earningsReport' => $earningsReport,
            'filters' => [
                'from' => $validated['from'] ?? null,
                'to' => $validated['to'] ?? null,
            ],
        ]);
    }
}
