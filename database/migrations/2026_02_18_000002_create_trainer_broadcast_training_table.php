<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_broadcast_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_broadcast_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_broadcast_training');
    }
};
