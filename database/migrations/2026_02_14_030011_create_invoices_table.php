<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_invoice_id')->unique()->index();
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->string('number')->nullable();
            $table->string('status', 50);
            $table->integer('amount_due_cents');
            $table->integer('amount_paid_cents');
            $table->string('currency', 3)->default('usd');
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('hosted_invoice_url', 500)->nullable();
            $table->string('invoice_pdf_url', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
