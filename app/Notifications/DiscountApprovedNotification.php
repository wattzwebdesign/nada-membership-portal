<?php

namespace App\Notifications;

use App\Models\DiscountRequest;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class DiscountApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels, UsesEmailTemplate;

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
            'user_name' => $notifiable->name,
            'discount_code' => $this->discountRequest->discount_code,
        ], fn () => (new MailMessage)
            ->subject('Discount Request Approved!')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Great news! Your discount request has been approved.')
            ->line("Discount Code: {$this->discountRequest->discount_code}")
            ->line('You can apply this code during checkout to receive your discount.')
            ->action('View Membership Plans', url('/membership'))
            ->line('Thank you for being part of the NADA community!'));
    }
}
