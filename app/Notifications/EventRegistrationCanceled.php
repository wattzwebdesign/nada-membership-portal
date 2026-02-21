<?php

namespace App\Notifications;

use App\Models\EventRegistration;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRegistrationCanceled extends Notification
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

        return $this->buildFromTemplate('event_registration_canceled', [
            'user_name' => $this->registration->full_name,
            'event_title' => $event->title,
            'event_date' => $event->start_date->format('F j, Y'),
            'registration_number' => $this->registration->registration_number,
        ], fn () => (new MailMessage)
            ->subject('Event Registration Canceled')
            ->greeting("Hello {$this->registration->full_name},")
            ->line('Your event registration has been canceled.')
            ->line("Event: {$event->title}")
            ->line("Date: {$event->start_date->format('F j, Y')}")
            ->line("Registration #: {$this->registration->registration_number}")
            ->line('If you paid for this registration, a refund will be processed.')
            ->action('Browse Events', url('/events'))
            ->line('We hope to see you at a future event!'));
    }
}
