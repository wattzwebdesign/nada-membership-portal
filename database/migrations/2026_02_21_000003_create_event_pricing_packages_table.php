<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_pricing_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_pricing_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_cents');
            $table->unsignedInteger('member_price_cents')->nullable();
            $table->unsignedInteger('early_bird_price_cents')->nullable();
            $table->unsignedInteger('early_bird_member_price_cents')->nullable();
            $table->dateTime('early_bird_deadline')->nullable();
            $table->unsignedInteger('max_quantity')->nullable();
            $table->unsignedInteger('quantity_sold')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_pricing_packages');
    }
};
