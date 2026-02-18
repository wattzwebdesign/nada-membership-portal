<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpcomingRenewalNotification extends Notification implements ShouldQueue
{
    use Queueable, UsesEmailTemplate;

    public function __construct(
        public Subscription $subscription,
        public int $daysUntilRenewal,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $renewalDate = $this->subscription->current_period_end->format('F j, Y');
        $planName = $this->subscription->plan?->name ?? 'NADA Membership';
        $amount = $this->subscription->plan?->price_cents
            ? '$' . number_format($this->subscription->plan->price_cents / 100, 2)
            : null;

        return $this->buildFromTemplate('upcoming_renewal', [
            'user_name' => $notifiable->name,
            'renewal_date' => $renewalDate,
            'days_until_renewal' => $this->daysUntilRenewal,
            'plan_name' => $planName,
        ], function () use ($notifiable, $renewalDate, $planName, $amount) {
            $message = (new MailMessage)
                ->subject("Membership Renewal Coming Up — {$renewalDate}")
                ->greeting("Hello {$notifiable->name},")
                ->line("Your **{$planName}** membership will renew on **{$renewalDate}**.");

            if ($amount) {
                $message->line("Amount: {$amount}");
            }

            return $message
                ->line('No action is needed — your card on file will be charged automatically.')
                ->line('If you need to update your payment method or have questions about your membership, visit your billing page.')
                ->action('Manage Billing', url('/membership/billing'))
                ->line('Thank you for being a NADA member!');
        });
    }
}
