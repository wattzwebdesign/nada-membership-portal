<?php

namespace App\Notifications;

use App\Models\VendorApplication;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApplicationSubmittedNotification extends Notification
{
    use UsesEmailTemplate;

    public function __construct(
        public VendorApplication $application,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildFromTemplate('vendor_application_submitted', [
            'applicant_name' => "{$this->application->first_name} {$this->application->last_name}",
            'applicant_email' => $this->application->email,
            'business_name' => $this->application->business_name,
            'website' => $this->application->website,
            'application_id' => $this->application->id,
        ], fn () => (new MailMessage)
            ->subject('New Vendor Application')
            ->greeting('Hello Admin,')
            ->line('A new vendor application has been submitted and requires your review.')
            ->line("Applicant: {$this->application->first_name} {$this->application->last_name}")
            ->line("Business: {$this->application->business_name}")
            ->line("Email: {$this->application->email}")
            ->when($this->application->website, fn ($msg) => $msg->line("Website: {$this->application->website}"))
            ->action('Review Application', url("/admin/vendor-applications/{$this->application->id}"))
            ->line('Please review and process this application.'));
    }
}
