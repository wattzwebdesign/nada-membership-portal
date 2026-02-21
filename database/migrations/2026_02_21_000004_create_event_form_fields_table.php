<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('label');
            $table->string('name');
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->string('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('conditional_on_field_id')->nullable()->constrained('event_form_fields')->nullOnDelete();
            $table->string('conditional_operator')->nullable();
            $table->string('conditional_value')->nullable();
            $table->foreignId('conditional_on_package_id')->nullable()->constrained('event_pricing_packages')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_form_fields');
    }
};
