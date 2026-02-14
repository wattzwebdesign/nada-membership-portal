<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionConfirmedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Subscription $subscription,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('subscription_confirmed', [
            'user_name' => $notifiable->name,
            'plan_name' => $this->subscription->plan->name,
        ], fn () => (new MailMessage)
            ->subject('Subscription Confirmed')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your NADA membership subscription has been confirmed.')
            ->line("Plan: {$this->subscription->plan->name}")
            ->line('Status: Active')
            ->action('View Membership', url('/membership'))
            ->line('Thank you for becoming a NADA member!'));
    }
}
