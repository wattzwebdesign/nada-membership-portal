<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_settings', function (Blueprint $table) {
            $table->string('type')->default('trainer')->after('id');
            $table->renameColumn('trainer_id', 'user_id');
            $table->renameColumn('trainer_percentage', 'payee_percentage');
        });

        // Seed a global vendor default row
        \App\Models\PayoutSetting::create([
            'type' => 'vendor',
            'user_id' => null,
            'platform_percentage' => 15.00,
            'payee_percentage' => 85.00,
            'is_active' => true,
            'notes' => 'Global default for vendor payouts',
        ]);
    }

    public function down(): void
    {
        // Remove the vendor default row
        \App\Models\PayoutSetting::where('type', 'vendor')->whereNull('user_id')->delete();

        Schema::table('payout_settings', function (Blueprint $table) {
            $table->renameColumn('user_id', 'trainer_id');
            $table->renameColumn('payee_percentage', 'trainer_percentage');
            $table->dropColumn('type');
        });
    }
};
