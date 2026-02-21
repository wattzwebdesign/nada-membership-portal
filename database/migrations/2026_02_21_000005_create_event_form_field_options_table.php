<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_form_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_form_field_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->integer('price_adjustment_cents')->nullable();
            $table->integer('member_price_adjustment_cents')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_form_field_options');
    }
};
