<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class SubscriptionCanceledNotification extends Notification implements ShouldQueue
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
            ->subject('Subscription Canceled')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your NADA membership subscription has been canceled.')
            ->line("Your access will remain active until: {$this->subscription->current_period_end->format('F j, Y')}")
            ->line('You can resubscribe at any time to regain full membership benefits.')
            ->action('Resubscribe', url('/membership'))
            ->line('We hope to see you back soon.');
    }
}
