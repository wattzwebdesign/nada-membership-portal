<?php

namespace App\Notifications;

use App\Models\TrainerApplication;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainerApplicationApprovedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public TrainerApplication $application,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('trainer_application_approved', [
            'user_name' => $notifiable->name,
        ], fn () => (new MailMessage)
            ->subject('Trainer Application Approved!')
            ->greeting("Congratulations {$notifiable->name}!")
            ->line('Your application to become a NADA trainer has been approved!')
            ->line('You now have access to trainer features, including the ability to create and manage training sessions.')
            ->action('Go to Trainer Dashboard', url('/dashboard'))
            ->line('Welcome to the NADA trainer community!'));
    }
}
