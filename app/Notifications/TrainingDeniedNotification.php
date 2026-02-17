<?php

namespace App\Notifications;

use App\Models\Training;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingDeniedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Training $training,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('training_denied', [
            'trainer_name' => $notifiable->full_name ?? $notifiable->email,
            'training_title' => $this->training->title,
            'denied_reason' => $this->training->denied_reason ?? 'No reason provided.',
        ], fn () => (new MailMessage)
            ->subject("Training Not Approved: {$this->training->title}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("Your training submission has not been approved.")
            ->line("Training: {$this->training->title}")
            ->line("Reason: " . ($this->training->denied_reason ?? 'No reason provided.'))
            ->line('You may edit your training and resubmit it for review.')
            ->line('If you have questions, please contact the NADA team.'));
    }
}
