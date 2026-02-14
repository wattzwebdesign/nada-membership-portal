<?php

namespace App\Notifications;

use App\Models\TrainingRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class TrainingRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

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

        return (new MailMessage)
            ->subject('Training Registration Confirmed')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your training registration has been confirmed.')
            ->line("Training: {$training->title}")
            ->line("Date: {$training->start_date->format('F j, Y')}")
            ->line("Location: {$training->location}")
            ->action('View Training Details', url("/trainings/{$training->id}"))
            ->line('We look forward to seeing you there!');
    }
}
