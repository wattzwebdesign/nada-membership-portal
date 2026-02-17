<?php

use App\Enums\TrainingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->boolean('is_group')->default(false)->after('status');
            $table->text('denied_reason')->nullable()->after('is_group');
        });

        // Convert existing 'draft' status rows to 'pending_approval'
        DB::table('trainings')->where('status', 'draft')->update(['status' => 'pending_approval']);
    }

    public function down(): void
    {
        // Convert back to 'draft'
        DB::table('trainings')->where('status', 'pending_approval')->update(['status' => 'draft']);

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn(['is_group', 'denied_reason']);
        });
    }
};
