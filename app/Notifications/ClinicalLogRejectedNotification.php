<?php

namespace App\Notifications;

use App\Models\ClinicalLog;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicalLogRejectedNotification extends Notification
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
        $notes = $this->clinicalLog->reviewer_notes ?? 'No additional notes provided.';

        return $this->buildFromTemplate('clinical_log_rejected', [
            'user_name' => $notifiable->full_name,
            'reviewer_notes' => $notes,
        ], fn () => (new MailMessage)
            ->subject('Your Clinical Log Book Needs Revision')
            ->greeting("Hello {$notifiable->full_name},")
            ->line('Your clinical log book has been reviewed and requires revision.')
            ->line("Reviewer notes: {$notes}")
            ->line('Please update your log book entries and resubmit for review.')
            ->action('View Your Log Book', url("/clinical-logs/{$this->clinicalLog->id}"))
            ->line('If you have questions, please contact your assigned trainer.'));
    }
}
