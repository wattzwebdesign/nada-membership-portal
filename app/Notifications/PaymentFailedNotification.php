<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Failed - Action Required')
            ->greeting("Hello {$notifiable->name},")
            ->line('We were unable to process your recent payment.')
            ->line("Amount: \${$this->invoice->amount}")
            ->line('Please update your payment method to avoid any interruption to your membership.')
            ->action('Update Payment Method', url('/membership/payment-method'))
            ->line('If you believe this is an error, please contact our support team.');
    }
}
