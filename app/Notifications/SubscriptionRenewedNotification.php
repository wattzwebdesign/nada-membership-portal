<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels, UsesEmailTemplate;

    public function __construct(
        public Subscription $subscription,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('subscription_renewed', [
            'user_name' => $notifiable->name,
            'plan_name' => $this->subscription->plan->name,
            'renewal_date' => $this->subscription->current_period_end->format('F j, Y'),
        ], fn () => (new MailMessage)
            ->subject('Membership Renewed')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your NADA membership has been successfully renewed.')
            ->line("Plan: {$this->subscription->plan->name}")
            ->line("Next renewal date: {$this->subscription->current_period_end->format('F j, Y')}")
            ->action('View Membership', url('/membership'))
            ->line('Thank you for continuing your membership with NADA!'));
    }
}
