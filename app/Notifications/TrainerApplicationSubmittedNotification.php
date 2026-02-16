<?php

namespace App\Notifications;

use App\Models\TrainerApplication;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainerApplicationSubmittedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public TrainerApplication $application,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('trainer_application_submitted', [
            'applicant_name' => $this->application->user->full_name,
            'applicant_email' => $this->application->user->email,
            'application_id' => $this->application->id,
        ], fn () => (new MailMessage)
            ->subject('New Trainer Application')
            ->greeting('Hello Admin,')
            ->line('A new trainer application has been submitted and requires your review.')
            ->line("Applicant: {$this->application->user->full_name}")
            ->line("Email: {$this->application->user->email}")
            ->action('Review Application', url("/admin/trainer-applications/{$this->application->id}"))
            ->line('Please review and process this application.'));
    }
}
