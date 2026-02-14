<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public User $user,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('welcome', [
            'user_name' => $this->user->name,
            'user_email' => $this->user->email,
        ], fn () => (new MailMessage)
            ->subject('Welcome to NADA!')
            ->greeting("Hello {$this->user->name}!")
            ->line('Welcome to the National Acupuncture Detoxification Association membership portal.')
            ->line('We are thrilled to have you join our community of dedicated practitioners.')
            ->action('Visit Your Dashboard', url('/dashboard'))
            ->line('If you have any questions, please do not hesitate to reach out to our support team.'));
    }
}
