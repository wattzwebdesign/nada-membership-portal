<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainerBroadcastNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $emailSubject,
        public string $emailBody,
        public string $trainerName,
        public string $trainerEmail,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->emailSubject)
            ->replyTo($this->trainerEmail, $this->trainerName);

        $paragraphs = preg_split('/\n\s*\n/', trim($this->emailBody));

        foreach ($paragraphs as $paragraph) {
            $message->line(trim($paragraph));
        }

        $message->line("— {$this->trainerName}, NADA Registered Trainer");

        return $message;
    }
}
