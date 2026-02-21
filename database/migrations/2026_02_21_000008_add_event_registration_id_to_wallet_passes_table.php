<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_passes', function (Blueprint $table) {
            $table->foreignId('event_registration_id')
                ->nullable()
                ->after('training_registration_id')
                ->constrained('event_registrations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wallet_passes', function (Blueprint $table) {
            $table->dropForeign(['event_registration_id']);
            $table->dropColumn('event_registration_id');
        });
    }
};
