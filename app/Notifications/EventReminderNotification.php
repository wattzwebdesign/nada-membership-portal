<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Event $event,
        public EventRegistration $registration,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('event_reminder', [
            'user_name' => $this->registration->full_name,
            'event_title' => $this->event->title,
            'event_date' => $this->event->start_date->format('F j, Y'),
            'event_time' => $this->event->start_date->format('g:i A'),
            'event_location' => $this->event->location_display,
            'event_slug' => $this->event->slug,
            'registration_id' => $this->registration->id,
        ], fn () => (new MailMessage)
            ->subject('Event Reminder - Tomorrow!')
            ->greeting("Hello {$this->registration->full_name}!")
            ->line('This is a friendly reminder that your NADA event is tomorrow.')
            ->line("Event: {$this->event->title}")
            ->line("Date: {$this->event->start_date->format('F j, Y \\a\\t g:i A')}")
            ->line("Location: {$this->event->location_display}")
            ->action('View Registration', url("/events/{$this->event->slug}/confirmation/{$this->registration->id}"))
            ->line('Please arrive on time. We look forward to seeing you!'));
    }
}
