<?php

namespace App\Notifications;

use App\Models\Clinical;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ClinicalSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Clinical $clinical,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Clinical Submission')
            ->greeting('Hello Admin,')
            ->line('A new clinical submission has been received and requires your review.')
            ->line("Submitted by: {$this->clinical->user->name}")
            ->line("Email: {$this->clinical->user->email}")
            ->action('Review Submission', url("/admin/clinicals/{$this->clinical->id}"))
            ->line('Please review this clinical submission at your earliest convenience.');
    }
}
