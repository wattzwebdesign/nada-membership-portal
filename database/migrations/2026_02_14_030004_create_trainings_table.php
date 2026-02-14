<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('location_name')->nullable();
            $table->string('location_address', 500)->nullable();
            $table->string('virtual_link', 500)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('timezone', 50)->default('America/New_York');
            $table->unsignedInteger('max_attendees')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('currency', 3)->default('usd');
            $table->string('stripe_price_id')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
