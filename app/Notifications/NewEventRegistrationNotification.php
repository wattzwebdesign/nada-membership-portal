<?php

namespace App\Notifications;

use App\Models\EventRegistration;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEventRegistrationNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public EventRegistration $registration,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->registration->event;
        $registrationCount = $event->registrations()->count();

        return $this->buildFromTemplate('new_event_registration', [
            'admin_name' => $notifiable->name,
            'registrant_name' => $this->registration->full_name,
            'registrant_email' => $this->registration->email,
            'event_title' => $event->title,
            'event_date' => $event->start_date->format('F j, Y'),
            'registration_count' => $registrationCount,
            'registration_number' => $this->registration->registration_number,
        ], fn () => (new MailMessage)
            ->subject("New Event Registration: {$event->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("{$this->registration->full_name} ({$this->registration->email}) has registered for: {$event->title}")
            ->line("Date: {$event->start_date->format('F j, Y')}")
            ->line("Registration #: {$this->registration->registration_number}")
            ->action('View Registrations', url("/admin/event-registrations"))
            ->line("Total registrations: {$registrationCount}"));
    }
}
