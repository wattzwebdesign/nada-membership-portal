<?php

namespace App\Notifications;

use App\Models\Training;
use App\Models\TrainingRegistration;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingReminderNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Training $training,
        public TrainingRegistration $registration,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('training_reminder', [
            'user_name' => $notifiable->name,
            'training_title' => $this->training->title,
            'training_date' => $this->training->start_date->format('F j, Y'),
            'training_location' => $this->training->location,
            'training_id' => $this->training->id,
        ], fn () => (new MailMessage)
            ->subject('Training Reminder - Tomorrow!')
            ->greeting("Hello {$notifiable->name}!")
            ->line('This is a friendly reminder that your NADA training session is tomorrow.')
            ->line("Training: {$this->training->title}")
            ->line("Date: {$this->training->start_date->format('F j, Y')}")
            ->line("Location: {$this->training->location}")
            ->action('View Training Details', url("/trainings/{$this->training->id}"))
            ->line('Please arrive on time and bring any required materials.'));
    }
}
