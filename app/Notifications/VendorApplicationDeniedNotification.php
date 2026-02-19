<?php

namespace App\Notifications;

use App\Models\VendorApplication;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApplicationDeniedNotification extends Notification
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
        $message = (new MailMessage)
            ->subject('Vendor Application Update')
            ->greeting("Hello, {$this->application->first_name}.")
            ->line("We've reviewed your vendor application for \"{$this->application->business_name}\" and unfortunately we are unable to approve it at this time.");

        if ($this->application->admin_notes) {
            $message->line("Notes: {$this->application->admin_notes}");
        }

        $message->line('If you have questions, please contact our support team.');

        return $this->buildFromTemplate('vendor_application_denied', [
            'applicant_name' => "{$this->application->first_name} {$this->application->last_name}",
            'business_name' => $this->application->business_name,
            'admin_notes' => $this->application->admin_notes,
        ], fn () => $message);
    }
}
