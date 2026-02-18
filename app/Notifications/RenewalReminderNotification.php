<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenewalReminderNotification extends Notification implements ShouldQueue
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

        return $this->buildFromTemplate('renewal_reminder', [
            'user_name' => $notifiable->name,
            'renewal_date' => $renewalDate,
            'days_until_renewal' => $this->daysUntilRenewal,
        ], fn () => (new MailMessage)
            ->subject("Membership Renewal in {$this->daysUntilRenewal} Days - Card Needed")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your NADA membership renews on **{$renewalDate}**, but you don't have a card on file.")
            ->line('Please add a payment method to continue your membership without interruption.')
            ->action('Add a Card', url('/membership/billing'))
            ->line('If you no longer wish to renew, no action is needed — your membership will remain active until the end of the current period.'));
    }
}
