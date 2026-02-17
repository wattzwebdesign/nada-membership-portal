<?php

namespace App\Notifications;

use App\Models\Training;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingApprovedNotification extends Notification
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
        return $this->buildFromTemplate('training_approved', [
            'trainer_name' => $notifiable->full_name ?? $notifiable->email,
            'training_title' => $this->training->title,
            'training_date' => $this->training->start_date->format('M j, Y \a\t g:i A'),
        ], fn () => (new MailMessage)
            ->subject("Your Training Has Been Approved: {$this->training->title}")
            ->greeting("Hello {$notifiable->full_name},")
            ->line("Great news! Your training has been approved and is now published.")
            ->line("Training: {$this->training->title}")
            ->line("Date: {$this->training->start_date->format('M j, Y \a\t g:i A')}")
            ->line('Members can now find and register for your training.')
            ->line('Thank you for contributing to the NADA community!'));
    }
}
