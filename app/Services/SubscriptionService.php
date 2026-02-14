<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionService
{
    public function createFromStripe(array $stripeData, User $user): Subscription
    {
        $plan = Plan::where('stripe_price_id', $stripeData['plan']['id'] ?? $stripeData['items']['data'][0]['price']['id'] ?? null)->first();

        return Subscription::updateOrCreate(
            ['stripe_subscription_id' => $stripeData['id']],
            [
                'user_id' => $user->id,
                'plan_id' => $plan?->id,
                'stripe_price_id' => $stripeData['items']['data'][0]['price']['id'] ?? '',
                'status' => $stripeData['status'],
                'current_period_start' => isset($stripeData['current_period_start'])
                    ? \Carbon\Carbon::createFromTimestamp($stripeData['current_period_start'])
                    : null,
                'current_period_end' => isset($stripeData['current_period_end'])
                    ? \Carbon\Carbon::createFromTimestamp($stripeData['current_period_end'])
                    : null,
                'cancel_at_period_end' => $stripeData['cancel_at_period_end'] ?? false,
                'canceled_at' => isset($stripeData['canceled_at'])
                    ? \Carbon\Carbon::createFromTimestamp($stripeData['canceled_at'])
                    : null,
                'trial_ends_at' => isset($stripeData['trial_end'])
                    ? \Carbon\Carbon::createFromTimestamp($stripeData['trial_end'])
                    : null,
                'metadata' => $stripeData['metadata'] ?? null,
            ]
        );
    }

    public function updateFromStripe(array $stripeData): ?Subscription
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeData['id'])->first();

        if (!$subscription) {
            return null;
        }

        $subscription->update([
            'status' => $stripeData['status'],
            'current_period_start' => isset($stripeData['current_period_start'])
                ? \Carbon\Carbon::createFromTimestamp($stripeData['current_period_start'])
                : $subscription->current_period_start,
            'current_period_end' => isset($stripeData['current_period_end'])
                ? \Carbon\Carbon::createFromTimestamp($stripeData['current_period_end'])
                : $subscription->current_period_end,
            'cancel_at_period_end' => $stripeData['cancel_at_period_end'] ?? $subscription->cancel_at_period_end,
            'canceled_at' => isset($stripeData['canceled_at'])
                ? \Carbon\Carbon::createFromTimestamp($stripeData['canceled_at'])
                : $subscription->canceled_at,
        ]);

        return $subscription;
    }

    public function createInvoiceFromStripe(array $invoiceData, User $user): Invoice
    {
        return Invoice::updateOrCreate(
            ['stripe_invoice_id' => $invoiceData['id']],
            [
                'user_id' => $user->id,
                'stripe_subscription_id' => $invoiceData['subscription'] ?? null,
                'number' => $invoiceData['number'] ?? null,
                'status' => $invoiceData['status'],
                'amount_due' => ($invoiceData['amount_due'] ?? 0) / 100,
                'amount_paid' => ($invoiceData['amount_paid'] ?? 0) / 100,
                'currency' => $invoiceData['currency'] ?? 'usd',
                'period_start' => isset($invoiceData['period_start'])
                    ? \Carbon\Carbon::createFromTimestamp($invoiceData['period_start'])
                    : null,
                'period_end' => isset($invoiceData['period_end'])
                    ? \Carbon\Carbon::createFromTimestamp($invoiceData['period_end'])
                    : null,
                'paid_at' => $invoiceData['status'] === 'paid' ? now() : null,
                'hosted_invoice_url' => $invoiceData['hosted_invoice_url'] ?? null,
                'invoice_pdf_url' => $invoiceData['invoice_pdf'] ?? null,
            ]
        );
    }
}
