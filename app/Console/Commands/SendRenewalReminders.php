<?php

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\PaymentOverdueNotification;
use App\Notifications\RenewalReminderNotification;
use App\Services\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendRenewalReminders extends Command
{
    protected $signature = 'nada:send-renewal-reminders';
    protected $description = 'Send pre-renewal reminders (no card on file) and post-failure overdue reminders';

    public function handle(StripeService $stripeService): int
    {
        $this->info('Starting renewal reminders...');

        $preRenewalSent = $this->sendPreRenewalReminders($stripeService);
        $postFailureSent = $this->sendPostFailureReminders();

        $this->info("Done. Pre-renewal: {$preRenewalSent} sent. Post-failure: {$postFailureSent} sent.");

        return Command::SUCCESS;
    }

    private function sendPreRenewalReminders(StripeService $stripeService): int
    {
        $sent = 0;
        $now = now();

        // Windows: 13-15 days (14d reminder) and 2-4 days (3d reminder)
        $windows = [
            ['start' => 13, 'end' => 15, 'key' => 'pre_renewal_14d_sent', 'days' => 14],
            ['start' => 2, 'end' => 4, 'key' => 'pre_renewal_3d_sent', 'days' => 3],
        ];

        foreach ($windows as $window) {
            $subscriptions = Subscription::where('status', SubscriptionStatus::Active)
                ->where('cancel_at_period_end', false)
                ->whereBetween('current_period_end', [
                    $now->copy()->addDays($window['start'])->startOfDay(),
                    $now->copy()->addDays($window['end'])->endOfDay(),
                ])
                ->with('user')
                ->get();

            foreach ($subscriptions as $subscription) {
                if (!$subscription->user || !$subscription->user->stripe_customer_id) {
                    continue;
                }

                // Skip if already sent this reminder
                $metadata = $subscription->metadata ?? [];
                if (!empty($metadata[$window['key']])) {
                    continue;
                }

                // Check if customer has a card on file
                try {
                    if ($stripeService->customerHasPaymentMethod($subscription->user->stripe_customer_id)) {
                        continue; // Card exists, no reminder needed
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to check payment method for customer', [
                        'user_id' => $subscription->user_id,
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }

                $subscription->user->notify(new RenewalReminderNotification($subscription, $window['days']));

                $metadata[$window['key']] = now()->toIso8601String();
                $subscription->update(['metadata' => $metadata]);

                Log::info('Pre-renewal reminder sent', [
                    'user_id' => $subscription->user_id,
                    'subscription_id' => $subscription->id,
                    'type' => $window['key'],
                    'renewal_date' => $subscription->current_period_end->toDateString(),
                ]);
                $this->info("  Pre-renewal {$window['days']}d reminder sent to {$subscription->user->email}");
                $sent++;
            }
        }

        return $sent;
    }

    private function sendPostFailureReminders(): int
    {
        $sent = 0;
        $now = now();

        $subscriptions = Subscription::where('status', SubscriptionStatus::PastDue)
            ->with('user')
            ->get();

        $thresholds = [
            ['min' => 3, 'max' => 6, 'key' => 'post_failure_3d_sent'],
            ['min' => 7, 'max' => 10, 'key' => 'post_failure_7d_sent'],
            ['min' => 14, 'max' => 17, 'key' => 'post_failure_14d_sent'],
        ];

        foreach ($subscriptions as $subscription) {
            if (!$subscription->user) {
                continue;
            }

            // Find the most recent unpaid invoice for this subscription
            $invoice = Invoice::where('stripe_subscription_id', $subscription->stripe_subscription_id)
                ->where('status', 'open')
                ->orderByDesc('created_at')
                ->first();

            if (!$invoice) {
                continue;
            }

            $daysSinceFailure = (int) $invoice->created_at->diffInDays($now);
            $metadata = $subscription->metadata ?? [];

            foreach ($thresholds as $threshold) {
                if ($daysSinceFailure >= $threshold['min'] && $daysSinceFailure <= $threshold['max']) {
                    if (!empty($metadata[$threshold['key']])) {
                        continue; // Already sent
                    }

                    $subscription->user->notify(new PaymentOverdueNotification($subscription, $invoice));

                    $metadata[$threshold['key']] = now()->toIso8601String();
                    $subscription->update(['metadata' => $metadata]);

                    Log::info('Post-failure reminder sent', [
                        'user_id' => $subscription->user_id,
                        'subscription_id' => $subscription->id,
                        'invoice_id' => $invoice->id,
                        'type' => $threshold['key'],
                        'days_since_failure' => $daysSinceFailure,
                    ]);
                    $this->info("  Post-failure {$threshold['min']}d reminder sent to {$subscription->user->email}");
                    $sent++;
                }
            }
        }

        return $sent;
    }
}
