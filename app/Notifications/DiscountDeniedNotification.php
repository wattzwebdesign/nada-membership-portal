<?php

namespace App\Notifications;

use App\Models\DiscountRequest;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscountDeniedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public DiscountRequest $discountRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('discount_denied', [
            'user_name' => $notifiable->name,
        ], fn () => (new MailMessage)
            ->subject('Discount Request Update')
            ->greeting("Hello {$notifiable->name},")
            ->line('We have reviewed your discount request and unfortunately we are unable to approve it at this time.')
            ->line('If you believe this decision was made in error or if your circumstances have changed, please feel free to submit a new request.')
            ->action('View Membership Plans', url('/membership'))
            ->line('Thank you for your understanding.'));
    }
}
