<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registration_pricing_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_pricing_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_category_id')->nullable()->constrained('event_pricing_categories')->nullOnDelete();
            $table->unsignedInteger('unit_price_cents');
            $table->boolean('is_member_pricing')->default(false);
            $table->boolean('is_early_bird')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registration_pricing_package');
    }
};
