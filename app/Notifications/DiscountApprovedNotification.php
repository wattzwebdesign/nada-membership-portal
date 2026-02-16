<?php

namespace App\Notifications;

use App\Models\DiscountRequest;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiscountApprovedNotification extends Notification
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
        return $this->buildFromTemplate('discount_approved', [
            'user_name' => $notifiable->full_name,
            'discount_type' => $this->discountRequest->discount_type->label(),
        ], fn () => (new MailMessage)
            ->subject('Discount Request Approved!')
            ->greeting("Hello {$notifiable->full_name}!")
            ->line('Great news! Your ' . $this->discountRequest->discount_type->label() . ' discount request has been approved.')
            ->line('Discounted membership plans are now available in your portal. Visit your Membership Plans page to view and activate your discounted rate.')
            ->action('View Membership Plans', url('/membership/plans'))
            ->line('Thank you for being part of the NADA community!'));
    }
}
