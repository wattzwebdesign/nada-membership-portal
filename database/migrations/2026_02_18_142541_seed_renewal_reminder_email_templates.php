<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $templates = [
            [
                'key' => 'upcoming_renewal',
                'name' => 'Upcoming Renewal Reminder',
                'description' => 'Sent 30 and 7 days before renewal to all active members.',
                'subject' => 'Membership Renewal Coming Up — {{renewal_date}}',
                'greeting' => 'Hello {{user_name}},',
                'body' => "Your **{{plan_name}}** membership will renew on **{{renewal_date}}**.\nNo action is needed — your card on file will be charged automatically.\nIf you need to update your payment method or have questions about your membership, visit your billing page.",
                'action_text' => 'Manage Billing',
                'action_url' => '/membership/billing',
                'outro' => 'Thank you for being a NADA member!',
                'available_variables' => json_encode(['user_name', 'renewal_date', 'days_until_renewal', 'plan_name']),
            ],
            [
                'key' => 'renewal_reminder',
                'name' => 'Renewal Reminder (No Card)',
                'description' => 'Sent 14 and 3 days before renewal when no card is on file.',
                'subject' => 'Membership Renewal in {{days_until_renewal}} Days — Card Needed',
                'greeting' => 'Hello {{user_name}},',
                'body' => "Your NADA membership renews on **{{renewal_date}}**, but you don't have a card on file.\nPlease add a payment method to continue your membership without interruption.",
                'action_text' => 'Add a Card',
                'action_url' => '/membership/billing',
                'outro' => 'If you no longer wish to renew, no action is needed — your membership will remain active until the end of the current period.',
                'available_variables' => json_encode(['user_name', 'renewal_date', 'days_until_renewal']),
            ],
            [
                'key' => 'payment_overdue',
                'name' => 'Payment Overdue',
                'description' => 'Sent 3, 7, and 14 days after a failed renewal payment.',
                'subject' => 'Payment Overdue — Action Required',
                'greeting' => 'Hello {{user_name}},',
                'body' => "Your membership payment of **{{amount}}** failed on {{failed_date}}.\nPlease pay the outstanding invoice or add a new card to keep your membership active.",
                'action_text' => 'Pay Now',
                'action_url' => '/membership/invoices',
                'outro' => 'You can also add a new card at your billing page — Stripe will automatically retry the payment. If you need help, contact financial@acudetox.com.',
                'available_variables' => json_encode(['user_name', 'amount', 'failed_date']),
            ],
            [
                'key' => 'payment_method_removed',
                'name' => 'Payment Method Removed',
                'description' => 'Sent when a member removes their card on file.',
                'subject' => 'Your Card Has Been Removed',
                'greeting' => 'Hello {{user_name}},',
                'body' => "Your card on file has been removed. Auto-payments for your membership are now stopped.\nYour membership remains active until the end of your current billing period.\nTo resume auto-payments, add a new card before your next renewal date.",
                'action_text' => 'Add a Card',
                'action_url' => '/membership/billing',
                'outro' => 'If you did not make this change, please contact our support team immediately.',
                'available_variables' => json_encode(['user_name']),
            ],
        ];

        foreach ($templates as $template) {
            if (DB::table('email_templates')->where('key', $template['key'])->exists()) {
                continue;
            }

            DB::table('email_templates')->insert(array_merge($template, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        DB::table('email_templates')->whereIn('key', [
            'upcoming_renewal',
            'renewal_reminder',
            'payment_overdue',
            'payment_method_removed',
        ])->delete();
    }
};
