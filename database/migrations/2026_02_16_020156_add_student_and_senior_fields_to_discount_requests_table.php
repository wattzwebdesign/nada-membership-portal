<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_requests', function (Blueprint $table) {
            $table->string('school_name')->nullable()->after('proof_description');
            $table->unsignedTinyInteger('years_remaining')->nullable()->after('school_name');
            $table->date('date_of_birth')->nullable()->after('years_remaining');
        });
    }

    public function down(): void
    {
        Schema::table('discount_requests', function (Blueprint $table) {
            $table->dropColumn(['school_name', 'years_remaining', 'date_of_birth']);
        });
    }
};
