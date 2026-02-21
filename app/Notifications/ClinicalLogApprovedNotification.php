<?php

namespace App\Notifications;

use App\Models\ClinicalLog;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicalLogApprovedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public ClinicalLog $clinicalLog,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('clinical_log_approved', [
            'user_name' => $notifiable->full_name,
            'total_hours' => $this->clinicalLog->total_hours,
        ], fn () => (new MailMessage)
            ->subject('Your Clinical Log Book Has Been Approved')
            ->greeting("Hello {$notifiable->full_name}!")
            ->line('Your clinical log book has been reviewed and approved.')
            ->line('Your NADA certificate will be issued shortly.')
            ->action('View Your Certificates', url('/certificates'))
            ->line('Thank you for completing your clinical hours.'));
    }
}
