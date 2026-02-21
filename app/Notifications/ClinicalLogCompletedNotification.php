<?php

namespace App\Notifications;

use App\Models\ClinicalLog;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicalLogCompletedNotification extends Notification
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
        $isTrainer = $this->clinicalLog->trainer_id && $notifiable instanceof \App\Models\User;
        $recipientName = $isTrainer ? $notifiable->full_name : 'Admin';
        $reviewUrl = $isTrainer
            ? url("/trainer/clinical-logs/{$this->clinicalLog->id}")
            : url("/admin/clinical-logs/{$this->clinicalLog->id}");

        return $this->buildFromTemplate('clinical_log_completed', [
            'recipient_name' => $recipientName,
            'member_name' => $this->clinicalLog->user->full_name,
            'total_hours' => $this->clinicalLog->total_hours,
        ], fn () => (new MailMessage)
            ->subject('Clinical Log Book Ready for Review')
            ->greeting("Hello {$recipientName},")
            ->line("{$this->clinicalLog->user->full_name} has completed their clinical log book with {$this->clinicalLog->total_hours} hours and submitted it for your review.")
            ->action('Review Log Book', $reviewUrl)
            ->line('Please review this clinical log book at your earliest convenience.'));
    }
}
