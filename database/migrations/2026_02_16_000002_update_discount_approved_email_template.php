<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $template = EmailTemplate::where('key', 'discount_approved')->first();

        if ($template) {
            $template->update([
                'body' => "Great news! Your {{discount_type}} discount request has been approved.\nDiscounted membership plans are now available in your portal. Visit your Membership Plans page to view and activate your discounted rate.",
                'action_url' => '/membership/plans',
                'available_variables' => ['user_name', 'discount_type'],
            ]);
        }
    }

    public function down(): void
    {
        $template = EmailTemplate::where('key', 'discount_approved')->first();

        if ($template) {
            $template->update([
                'body' => "Great news! Your discount request has been approved.\nDiscount Code: {{discount_code}}\nYou can apply this code during checkout to receive your discount.",
                'action_url' => '/membership',
                'available_variables' => ['user_name', 'discount_code'],
            ]);
        }
    }
};
