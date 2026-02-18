<?php

namespace App\Notifications;

use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentMethodRemovedNotification extends Notification implements ShouldQueue
{
    use Queueable, UsesEmailTemplate;

    public function __construct() {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('payment_method_removed', [
            'user_name' => $notifiable->name,
        ], fn () => (new MailMessage)
            ->subject('Your Card Has Been Removed')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your card on file has been removed. Auto-payments for your membership are now stopped.')
            ->line('Your membership remains active until the end of your current billing period.')
            ->line('To resume auto-payments, add a new card before your next renewal date.')
            ->action('Add a Card', url('/membership/billing'))
            ->line('If you did not make this change, please contact our support team immediately.'));
    }
}
