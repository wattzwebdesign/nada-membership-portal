<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('stripe_product_id')->index();
            $table->string('stripe_price_id')->unique()->index();
            $table->unsignedInteger('price_cents');
            $table->string('currency', 3)->default('usd');
            $table->string('billing_interval')->default('year');
            $table->unsignedTinyInteger('billing_interval_count')->default(1);
            $table->string('plan_type');
            $table->string('role_required')->nullable();
            $table->string('discount_required')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
