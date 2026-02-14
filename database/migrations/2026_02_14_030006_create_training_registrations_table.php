<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('registered');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('marked_complete_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->unsignedInteger('amount_paid_cents')->default(0);
            $table->foreignId('certificate_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['training_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_registrations');
    }
};
