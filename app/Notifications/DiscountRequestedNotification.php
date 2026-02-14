<?php

namespace App\Notifications;

use App\Models\DiscountRequest;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class DiscountRequestedNotification extends Notification implements ShouldQueue
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
        return $this->buildFromTemplate('discount_requested', [
            'requester_name' => $this->discountRequest->user->name,
            'requester_email' => $this->discountRequest->user->email,
            'reason' => $this->discountRequest->reason,
            'request_id' => $this->discountRequest->id,
        ], fn () => (new MailMessage)
            ->subject('New Discount Request')
            ->greeting('Hello Admin,')
            ->line('A new discount request has been submitted and requires your review.')
            ->line("Requested by: {$this->discountRequest->user->name}")
            ->line("Email: {$this->discountRequest->user->email}")
            ->line("Reason: {$this->discountRequest->reason}")
            ->action('Review Request', url("/admin/discount-requests/{$this->discountRequest->id}"))
            ->line('Please review and approve or deny this request.'));
    }
}
