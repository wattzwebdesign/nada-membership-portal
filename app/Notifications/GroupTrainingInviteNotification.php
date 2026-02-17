<?php

namespace App\Notifications;

use App\Models\Training;
use App\Models\TrainingInvitee;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GroupTrainingInviteNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Training $training,
        public TrainingInvitee $invitee,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $location = $this->training->location_name ?? $this->training->location_address ?? 'Virtual';

        return $this->buildFromTemplate('group_training_invite', [
            'training_title' => $this->training->title,
            'trainer_name' => $this->training->trainer->full_name ?? $this->training->trainer->email,
            'training_date' => $this->training->start_date->format('M j, Y \a\t g:i A'),
            'training_location' => $location,
            'training_id' => $this->training->id,
            'token' => $this->invitee->token,
        ], fn () => (new MailMessage)
            ->subject("You're Invited: {$this->training->title}")
            ->greeting('Hello,')
            ->line("You have been invited to a NADA training.")
            ->line("Training: {$this->training->title}")
            ->line("Trainer: " . ($this->training->trainer->full_name ?? $this->training->trainer->email))
            ->line("Date: {$this->training->start_date->format('M j, Y \a\t g:i A')}")
            ->line("Location: {$location}")
            ->action('View Training', url("/trainings/{$this->training->id}?token={$this->invitee->token}"))
            ->line('You must login with this email address in order to register for this training.'));
    }
}
