<?php

namespace App\Notifications;

use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class PaymentMethodUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels, UsesEmailTemplate;

    public function __construct() {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('payment_method_updated', [
            'user_name' => $notifiable->name,
        ], fn () => (new MailMessage)
            ->subject('Payment Method Updated')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your payment method has been successfully updated.')
            ->line('All future charges will be applied to your new payment method.')
            ->action('View Account', url('/membership'))
            ->line('If you did not make this change, please contact our support team immediately.'));
    }
}
