<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('email_templates')->where('key', 'invoice_created')->exists()) {
            return;
        }

        DB::table('email_templates')->insert([
            'key' => 'invoice_created',
            'name' => 'New Invoice',
            'description' => 'Sent to members when a new invoice is created.',
            'subject' => 'New Invoice {{invoice_number}}',
            'greeting' => 'Hello {{user_name}},',
            'body' => "A new invoice has been created for your account.\nInvoice: {{invoice_number}}\nAmount Due: {{amount_due}}\nPlease review and submit payment at your earliest convenience.",
            'action_text' => 'View Invoice',
            'action_url' => '/invoices/{{invoice_id}}',
            'outro' => 'If you have any questions about this invoice, please contact our support team.',
            'available_variables' => json_encode(['user_name', 'invoice_number', 'amount_due', 'invoice_id']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'invoice_created')->delete();
    }
};
