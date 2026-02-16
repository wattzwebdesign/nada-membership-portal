<?php

namespace App\Notifications;

use App\Models\TrainingRegistration;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingRegisteredNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public TrainingRegistration $registration,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $training = $this->registration->training;
        $location = collect([$training->location_name, $training->location_address])->filter()->implode(' — ') ?: 'See training details';

        return $this->buildFromTemplate('training_registered', [
            'user_name' => $notifiable->name,
            'training_title' => $training->title,
            'training_date' => $training->start_date->format('F j, Y'),
            'training_location' => $location,
            'training_id' => $training->id,
        ], fn () => (new MailMessage)
            ->subject('Training Registration Confirmed')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your training registration has been confirmed.')
            ->line("Training: {$training->title}")
            ->line("Date: {$training->start_date->format('F j, Y')}")
            ->line("Location: {$location}")
            ->action('View Training Details', url("/trainings/{$training->id}"))
            ->line('We look forward to seeing you there!'));
    }
}
