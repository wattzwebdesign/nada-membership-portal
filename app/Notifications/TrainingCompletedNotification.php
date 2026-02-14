<?php

namespace App\Notifications;

use App\Models\TrainingRegistration;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class TrainingCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels, UsesEmailTemplate;

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
            ->line('Your certificate will be available shortly.')
            ->action('View Your Dashboard', url('/dashboard'))
            ->line('Thank you for your commitment to the NADA protocol.'));
    }
}
