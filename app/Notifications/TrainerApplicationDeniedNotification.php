<?php

namespace App\Notifications;

use App\Models\TrainerApplication;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class TrainerApplicationDeniedNotification extends Notification implements ShouldQueue
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
        return $this->buildFromTemplate('trainer_application_denied', [
            'user_name' => $notifiable->name,
        ], fn () => (new MailMessage)
            ->subject('Trainer Application Update')
            ->greeting("Hello {$notifiable->name},")
            ->line('We have reviewed your trainer application and unfortunately we are unable to approve it at this time.')
            ->line('This may be due to insufficient training hours or other requirements that have not yet been met.')
            ->line('You are welcome to reapply once you have completed the necessary requirements.')
            ->action('View Requirements', url('/trainers/apply'))
            ->line('Thank you for your interest in becoming a NADA trainer.'));
    }
}
