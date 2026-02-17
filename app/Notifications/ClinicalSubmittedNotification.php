<?php

namespace App\Notifications;

use App\Filament\Resources\ClinicalResource;
use App\Models\Clinical;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicalSubmittedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public Clinical $clinical,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isTrainer = $this->clinical->trainer_id && $notifiable instanceof \App\Models\User;
        $recipientName = $isTrainer ? $notifiable->name : 'Admin';
        $reviewUrl = $isTrainer
            ? url("/trainer/clinicals/{$this->clinical->id}")
            : ClinicalResource::getUrl('view', ['record' => $this->clinical]);

        return $this->buildFromTemplate('clinical_submitted', [
            'recipient_name' => $recipientName,
            'submitter_name' => $this->clinical->user->name,
            'submitter_email' => $this->clinical->user->email,
            'clinical_id' => $this->clinical->id,
        ], fn () => (new MailMessage)
            ->subject('New Clinical Submission')
            ->greeting("Hello {$recipientName},")
            ->line('A new clinical submission has been received and requires your review.')
            ->line("Submitted by: {$this->clinical->user->name}")
            ->line("Email: {$this->clinical->user->email}")
            ->action('Review Submission', $reviewUrl)
            ->line('Please review this clinical submission at your earliest convenience.'));
    }
}
