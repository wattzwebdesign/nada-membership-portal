<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreement_signatures', function (Blueprint $table) {
            $table->unsignedInteger('amount_cents')->nullable()->after('consent_snapshot');
            $table->string('stripe_transaction_id')->nullable()->after('amount_cents');
        });
    }

    public function down(): void
    {
        Schema::table('agreement_signatures', function (Blueprint $table) {
            $table->dropColumn(['amount_cents', 'stripe_transaction_id']);
        });
    }
};
