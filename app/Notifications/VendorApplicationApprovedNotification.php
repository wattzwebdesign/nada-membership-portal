<?php

namespace App\Notifications;

use App\Models\VendorApplication;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApplicationApprovedNotification extends Notification
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
        return $this->buildFromTemplate('vendor_application_approved', [
            'applicant_name' => "{$this->application->first_name} {$this->application->last_name}",
            'business_name' => $this->application->business_name,
        ], fn () => (new MailMessage)
            ->subject('Your Vendor Application Has Been Approved!')
            ->greeting("Congratulations, {$this->application->first_name}!")
            ->line("Your vendor application for \"{$this->application->business_name}\" has been approved.")
            ->line('You can now set up your vendor profile and start listing products in the NADA marketplace.')
            ->action('Set Up Your Store', url('/vendor/profile'))
            ->line('Welcome to the NADA marketplace!'));
    }
}
