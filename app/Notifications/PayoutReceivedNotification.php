<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class PayoutReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public float $amount,
        public string $currency = 'USD',
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $formattedAmount = number_format($this->amount, 2);

        return (new MailMessage)
            ->subject('Payout Received!')
            ->greeting("Hello {$notifiable->name}!")
            ->line('A payout has been processed to your account.')
            ->line("Amount: \${$formattedAmount} {$this->currency}")
            ->line('The funds should appear in your bank account within a few business days.')
            ->action('View Payout History', url('/dashboard'))
            ->line('Thank you for being a valued NADA trainer!');
    }
}
