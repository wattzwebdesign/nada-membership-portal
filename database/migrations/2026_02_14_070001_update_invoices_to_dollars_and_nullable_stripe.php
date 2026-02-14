<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the unique index on stripe_invoice_id so manual invoices work
            $table->dropUnique(['stripe_invoice_id']);

            // Make Stripe fields nullable for manually-created invoices
            $table->string('stripe_invoice_id')->nullable()->change();

            // Add index back (non-unique)
            $table->index('stripe_invoice_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            // Convert cents columns to dollar decimals
            $table->decimal('amount_due', 10, 2)->default(0)->after('status');
            $table->decimal('amount_paid', 10, 2)->default(0)->after('amount_due');
        });

        // Migrate existing cent values to dollar values
        DB::table('invoices')->update([
            'amount_due' => DB::raw('amount_due_cents / 100.0'),
            'amount_paid' => DB::raw('amount_paid_cents / 100.0'),
        ]);

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['amount_due_cents', 'amount_paid_cents']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('amount_due_cents')->default(0)->after('status');
            $table->integer('amount_paid_cents')->default(0)->after('amount_due_cents');
        });

        DB::table('invoices')->update([
            'amount_due_cents' => DB::raw('amount_due * 100'),
            'amount_paid_cents' => DB::raw('amount_paid * 100'),
        ]);

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['amount_due', 'amount_paid']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['stripe_invoice_id']);
            $table->string('stripe_invoice_id')->nullable(false)->change();
            $table->unique('stripe_invoice_id');
        });
    }
};
