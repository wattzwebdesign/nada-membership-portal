<?php

namespace App\Notifications;

use App\Models\TrainingRegistration;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingCompletedNotification extends Notification
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

        return $this->buildFromTemplate('training_completed', [
            'user_name' => $notifiable->name,
            'training_title' => $training->title,
        ], fn () => (new MailMessage)
            ->subject('Training Completed!')
            ->greeting("Congratulations {$notifiable->name}!")
            ->line('You have successfully completed your NADA training session.')
            ->line("Training: {$training->title}")
            ->line('Please complete your 40 hours of clinicals. When you are done, submit them here.')
            ->action('Submit Clinicals', url('/clinicals/submit'))
            ->line('Thank you for your commitment to the NADA protocol.'));
    }
}
