<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // 'apple' or 'google'
            $table->string('serial_number')->unique();
            $table->string('pass_type_identifier')->nullable(); // Apple only
            $table->string('google_object_id')->nullable(); // Google only
            $table->string('authentication_token', 64);
            $table->timestamp('last_updated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_passes');
    }
};
