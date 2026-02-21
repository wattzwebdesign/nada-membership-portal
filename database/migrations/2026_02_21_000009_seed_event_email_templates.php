<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $templates = [
            [
                'key' => 'event_registration_confirmation',
                'name' => 'Event Registration Confirmation',
                'description' => 'Sent to attendee after successful event registration',
                'subject' => 'Registration Confirmed: {{event_title}}',
                'greeting' => 'Hello {{first_name}},',
                'body' => "Your registration for **{{event_title}}** has been confirmed!\n\nRegistration #: {{registration_number}}\nDate: {{event_date}}\nLocation: {{event_location}}\nTotal: {{total_amount}}\n\nYour QR code for check-in is available on your confirmation page.",
                'action_text' => 'View Registration',
                'action_url' => '{{confirmation_url}}',
                'outro' => 'If you need to cancel your registration, please visit your registrations page.',
                'available_variables' => ['first_name', 'last_name', 'event_title', 'registration_number', 'event_date', 'event_location', 'total_amount', 'confirmation_url'],
                'is_active' => true,
            ],
            [
                'key' => 'event_registration_canceled',
                'name' => 'Event Registration Canceled',
                'description' => 'Sent when an event registration is canceled',
                'subject' => 'Registration Canceled: {{event_title}}',
                'greeting' => 'Hello {{first_name}},',
                'body' => "Your registration for **{{event_title}}** has been canceled.\n\nRegistration #: {{registration_number}}\n\nIf you believe this was done in error, please contact us.",
                'action_text' => null,
                'action_url' => null,
                'outro' => 'Thank you for your interest in NADA events.',
                'available_variables' => ['first_name', 'last_name', 'event_title', 'registration_number'],
                'is_active' => true,
            ],
            [
                'key' => 'event_reminder',
                'name' => 'Event Reminder',
                'description' => 'Sent 24 hours before event starts',
                'subject' => 'Reminder: {{event_title}} is Tomorrow!',
                'greeting' => 'Hello {{first_name}},',
                'body' => "Just a friendly reminder that **{{event_title}}** is tomorrow!\n\nDate: {{event_date}}\nLocation: {{event_location}}\nRegistration #: {{registration_number}}\n\nDon't forget to bring your QR code for check-in.",
                'action_text' => 'View Registration',
                'action_url' => '{{confirmation_url}}',
                'outro' => 'We look forward to seeing you there!',
                'available_variables' => ['first_name', 'last_name', 'event_title', 'registration_number', 'event_date', 'event_location', 'confirmation_url'],
                'is_active' => true,
            ],
            [
                'key' => 'new_event_registration_admin',
                'name' => 'New Event Registration (Admin)',
                'description' => 'Sent to admin when a new event registration is made',
                'subject' => 'New Registration: {{event_title}}',
                'greeting' => 'New Event Registration',
                'body' => "A new registration has been received for **{{event_title}}**.\n\nAttendee: {{first_name}} {{last_name}}\nEmail: {{email}}\nRegistration #: {{registration_number}}\nAmount: {{total_amount}}",
                'action_text' => 'View in Admin',
                'action_url' => '{{admin_url}}',
                'outro' => null,
                'available_variables' => ['first_name', 'last_name', 'email', 'event_title', 'registration_number', 'total_amount', 'admin_url'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['key' => $template['key']],
                $template
            );
        }
    }

    public function down(): void
    {
        EmailTemplate::whereIn('key', [
            'event_registration_confirmation',
            'event_registration_canceled',
            'event_reminder',
            'new_event_registration_admin',
        ])->delete();
    }
};
