<?php

namespace App\Notifications;

use App\Models\Certificate;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class CertificateReadyNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels, UsesEmailTemplate;

    public function __construct(
        public Certificate $certificate,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('certificate_ready', [
            'user_name' => $notifiable->name,
            'certificate_number' => $this->certificate->certificate_number,
            'certificate_id' => $this->certificate->id,
        ], fn () => (new MailMessage)
            ->subject('Your NADA Certificate is Ready!')
            ->greeting("Congratulations {$notifiable->name}!")
            ->line('Your NADA certificate has been issued and is ready for download.')
            ->line("Certificate Number: {$this->certificate->certificate_number}")
            ->action('Download Certificate', url("/certificates/{$this->certificate->id}"))
            ->line('Thank you for your dedication to the NADA protocol.'));
    }
}
