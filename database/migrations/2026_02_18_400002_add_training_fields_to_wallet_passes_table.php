<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_passes', function (Blueprint $table) {
            $table->string('pass_category')->default('membership')->after('platform');
            $table->foreignId('training_registration_id')
                ->nullable()
                ->after('pass_category')
                ->constrained()
                ->cascadeOnDelete();

            $table->index(['user_id', 'platform', 'pass_category']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_passes', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'platform', 'pass_category']);
            $table->dropConstrainedForeignId('training_registration_id');
            $table->dropColumn('pass_category');
        });
    }
};
