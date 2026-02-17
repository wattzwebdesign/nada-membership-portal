<?php

namespace App\Notifications;

use App\Models\TrainingRegistration;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTrainingRegistrationNotification extends Notification
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
        $member = $this->registration->user;
        $registrationCount = $training->registrations()->count();

        return $this->buildFromTemplate('new_training_registration', [
            'trainer_name' => $notifiable->name,
            'member_name' => $member->name,
            'member_email' => $member->email,
            'training_title' => $training->title,
            'training_date' => $training->start_date->format('F j, Y'),
            'registration_count' => $registrationCount,
            'training_id' => $training->id,
        ], fn () => (new MailMessage)
            ->subject("New Registration: {$training->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("{$member->name} ({$member->email}) has registered for your training: {$training->title} on {$training->start_date->format('F j, Y')}.")
            ->action('View Attendees', url("/trainer/trainings/{$training->id}/attendees"))
            ->line("You now have {$registrationCount} registered attendee(s)."));
    }
}
