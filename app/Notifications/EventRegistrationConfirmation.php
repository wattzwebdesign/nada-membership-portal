<?php

namespace App\Notifications;

use App\Models\EventRegistration;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventRegistrationConfirmation extends Notification
{
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

        return (new MailMessage)
            ->subject('Event Registration Confirmed - ' . $event->title)
            ->view('emails.event-registration-confirmed', [
                'registration' => $this->registration,
                'event' => $event,
                'qrCodeUrl' => route('events.qr', $this->registration->qr_code_token),
                'confirmationUrl' => url("/events/{$event->slug}/confirmation/{$this->registration->id}"),
            ]);
    }
}
