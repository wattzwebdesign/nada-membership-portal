<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_device_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_pass_id')->constrained('wallet_passes')->cascadeOnDelete();
            $table->string('device_library_identifier');
            $table->string('push_token');
            $table->timestamps();

            $table->unique(['wallet_pass_id', 'device_library_identifier'], 'wallet_device_pass_device_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_device_registrations');
    }
};
