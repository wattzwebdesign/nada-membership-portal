<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('in_progress');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['trainer_id', 'status']);
        });

        Schema::create('clinical_log_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinical_log_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('location');
            $table->string('protocol');
            $table->decimal('hours', 5, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('clinical_log_id');
        });

        // Seed default clinical hours threshold
        \App\Models\SiteSetting::set('clinical_hours_threshold', '40');
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_log_entries');
        Schema::dropIfExists('clinical_logs');

        \App\Models\SiteSetting::where('key', 'clinical_hours_threshold')->delete();
    }
};
