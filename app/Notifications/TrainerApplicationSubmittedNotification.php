<?php

namespace App\Notifications;

use App\Models\TrainerApplication;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class TrainerApplicationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels, UsesEmailTemplate;

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
            'applicant_name' => $this->application->user->name,
            'applicant_email' => $this->application->user->email,
            'application_id' => $this->application->id,
        ], fn () => (new MailMessage)
            ->subject('New Trainer Application')
            ->greeting('Hello Admin,')
            ->line('A new trainer application has been submitted and requires your review.')
            ->line("Applicant: {$this->application->user->name}")
            ->line("Email: {$this->application->user->email}")
            ->action('Review Application', url("/admin/trainer-applications/{$this->application->id}"))
            ->line('Please review and process this application.'));
    }
}
