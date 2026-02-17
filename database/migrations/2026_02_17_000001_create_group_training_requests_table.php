<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_training_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users');
            $table->string('company_first_name');
            $table->string('company_last_name');
            $table->string('company_email');
            $table->string('training_name');
            $table->date('training_date');
            $table->string('training_city');
            $table->string('training_state', 2);
            $table->integer('cost_per_ticket_cents');
            $table->integer('number_of_tickets');
            $table->integer('transaction_fee_cents')->default(0);
            $table->integer('total_amount_cents');
            $table->string('stripe_checkout_session_id')->nullable()->index();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('status')->default('pending_payment');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_training_requests');
    }
};
