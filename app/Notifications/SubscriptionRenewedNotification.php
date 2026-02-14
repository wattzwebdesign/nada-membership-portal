<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Membership Renewed')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your NADA membership has been successfully renewed.')
            ->line("Plan: {$this->subscription->plan->name}")
            ->line("Next renewal date: {$this->subscription->current_period_end->format('F j, Y')}")
            ->action('View Membership', url('/membership'))
            ->line('Thank you for continuing your membership with NADA!');
    }
}
