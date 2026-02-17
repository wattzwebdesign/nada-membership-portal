<?php

namespace App\Notifications;

use App\Models\Clinical;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicalApprovedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Clinical $clinical,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('clinical_approved', [
            'user_name' => $notifiable->name,
            'clinical_id' => $this->clinical->id,
        ], fn () => (new MailMessage)
            ->subject('Your Clinical Submission Has Been Approved')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your clinical submission has been reviewed and approved.')
            ->line('Your NADA certificate will be issued shortly.')
            ->action('View Your Certificates', url('/certificates'))
            ->line('Thank you for completing your clinical hours.'));
    }
}
