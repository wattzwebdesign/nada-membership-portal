<?php

namespace App\Notifications;

use App\Models\Training;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingCanceledNotification extends Notification
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
        return $this->buildFromTemplate('training_canceled', [
            'user_name' => $notifiable->name,
            'training_title' => $this->training->title,
            'training_date' => $this->training->start_date->format('F j, Y'),
        ], fn () => (new MailMessage)
            ->subject('Training Canceled')
            ->greeting("Hello {$notifiable->name},")
            ->line('We regret to inform you that the following training session has been canceled.')
            ->line("Training: {$this->training->title}")
            ->line("Originally scheduled: {$this->training->start_date->format('F j, Y')}")
            ->line('If you have already paid, a refund will be processed automatically.')
            ->action('Browse Trainings', url('/trainings'))
            ->line('We apologize for any inconvenience.'));
    }
}
