<?php

namespace App\Notifications;

use App\Models\EventRegistration;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventCheckedInNotification extends Notification
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

        return $this->buildFromTemplate('event_checked_in', [
            'user_name' => $this->registration->full_name,
            'event_title' => $event->title,
            'checked_in_at' => $this->registration->checked_in_at->format('g:i A'),
        ], fn () => (new MailMessage)
            ->subject("Checked In: {$event->title}")
            ->greeting("Hello {$this->registration->full_name}!")
            ->line("You've been checked in to: {$event->title}")
            ->line("Check-in time: {$this->registration->checked_in_at->format('g:i A')}")
            ->line('Enjoy the event!'));
    }
}
