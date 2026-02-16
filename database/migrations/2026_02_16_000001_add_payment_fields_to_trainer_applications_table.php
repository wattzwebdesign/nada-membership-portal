<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainer_applications', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id')->nullable()->after('admin_notes');
            $table->foreignId('invoice_id')->nullable()->after('stripe_payment_intent_id')->constrained()->nullOnDelete();
            $table->integer('amount_paid_cents')->default(7500)->after('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::table('trainer_applications', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn(['stripe_payment_intent_id', 'invoice_id', 'amount_paid_cents']);
        });
    }
};
