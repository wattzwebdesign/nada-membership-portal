<?php

namespace App\Notifications;

use App\Models\EventRegistration;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRegistrationConfirmation extends Notification
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

        return $this->buildFromTemplate('event_registration_confirmed', [
            'user_name' => $this->registration->full_name,
            'event_title' => $event->title,
            'event_date' => $event->start_date->format('F j, Y'),
            'event_location' => $event->location_display,
            'registration_number' => $this->registration->registration_number,
            'event_slug' => $event->slug,
            'registration_id' => $this->registration->id,
        ], fn () => (new MailMessage)
            ->subject('Event Registration Confirmed')
            ->greeting("Hello {$this->registration->full_name}!")
            ->line('Your event registration has been confirmed.')
            ->line("Event: {$event->title}")
            ->line("Date: {$event->start_date->format('F j, Y')}")
            ->line("Location: {$event->location_display}")
            ->line("Registration #: {$this->registration->registration_number}")
            ->action('View Registration', url("/events/{$event->slug}/confirmation/{$this->registration->id}"))
            ->line('We look forward to seeing you there!'));
    }
}
