<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_order_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_profile_id')->constrained();
            $table->unsignedInteger('subtotal_cents');
            $table->decimal('platform_percentage', 5, 2);
            $table->decimal('vendor_percentage', 5, 2);
            $table->unsignedInteger('platform_fee_cents');
            $table->unsignedInteger('vendor_payout_cents');
            $table->string('status')->default('pending');
            $table->string('stripe_transfer_id')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_order_splits');
    }
};
