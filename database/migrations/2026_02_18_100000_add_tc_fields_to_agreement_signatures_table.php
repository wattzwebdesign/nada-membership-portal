<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreement_signatures', function (Blueprint $table) {
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->string('consent_context')->nullable()->after('user_agent');
            $table->string('context_reference_type')->nullable()->after('consent_context');
            $table->unsignedBigInteger('context_reference_id')->nullable()->after('context_reference_type');
            $table->longText('consent_snapshot')->nullable()->after('context_reference_id');
        });
    }

    public function down(): void
    {
        Schema::table('agreement_signatures', function (Blueprint $table) {
            $table->dropColumn([
                'user_agent',
                'consent_context',
                'context_reference_type',
                'context_reference_id',
                'consent_snapshot',
            ]);
        });
    }
};
