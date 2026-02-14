<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('platform_percentage', 5, 2)->default(20.00);
            $table->decimal('trainer_percentage', 5, 2)->default(80.00);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_settings');
    }
};
