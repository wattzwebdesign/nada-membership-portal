<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Invoice $invoice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('payment_failed', [
            'user_name' => $notifiable->name,
            'amount' => '$' . number_format($this->invoice->amount_due, 2),
        ], fn () => (new MailMessage)
            ->subject('Payment Failed - Action Required')
            ->greeting("Hello {$notifiable->name},")
            ->line('We were unable to process your recent payment.')
            ->line("Amount: \${$this->invoice->amount_due}")
            ->line('Please update your payment method to avoid any interruption to your membership.')
            ->action('Update Payment Method', url('/membership/billing'))
            ->line('If you believe this is an error, please contact our support team.'));
    }
}
