<?php

namespace App\Notifications;

use App\Models\Training;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingSubmittedNotification extends Notification
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
        return $this->buildFromTemplate('training_submitted', [
            'trainer_name' => $this->training->trainer->full_name ?? $this->training->trainer->email,
            'training_title' => $this->training->title,
            'training_date' => $this->training->start_date->format('M j, Y \a\t g:i A'),
            'training_id' => $this->training->id,
        ], fn () => (new MailMessage)
            ->subject("New Training Submitted: {$this->training->title}")
            ->greeting('Hello Admin,')
            ->line("A trainer has submitted a new training for your review.")
            ->line("Trainer: " . ($this->training->trainer->full_name ?? $this->training->trainer->email))
            ->line("Training: {$this->training->title}")
            ->line("Date: {$this->training->start_date->format('M j, Y \a\t g:i A')}")
            ->action('Review Training', url("/admin/trainings/{$this->training->id}/edit"))
            ->line('Please review and approve or deny this training.'));
    }
}
