<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainerContactNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public ?string $senderPhone,
        public string $senderMessage,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('New Contact Message from ' . $this->senderName)
            ->replyTo($this->senderEmail, $this->senderName)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('You have received a new message through your NADA trainer profile.')
            ->line('**From:** ' . $this->senderName)
            ->line('**Email:** ' . $this->senderEmail);

        if ($this->senderPhone) {
            $message->line('**Phone:** ' . $this->senderPhone);
        }

        $message->line('**Message:**')
            ->line($this->senderMessage);

        return $message;
    }
}
