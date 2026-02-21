<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();

            // Location
            $table->string('location_name')->nullable();
            $table->string('location_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->default('US');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('virtual_link')->nullable();

            // Dates
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('timezone')->default('America/New_York');
            $table->dateTime('registration_start_date')->nullable();
            $table->dateTime('registration_end_date')->nullable();

            // Capacity
            $table->unsignedInteger('max_attendees')->nullable();

            // Status
            $table->string('status')->default('draft');

            // Display
            $table->string('featured_image_path')->nullable();
            $table->boolean('is_featured')->default(false);

            // Contact
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('organizer_name')->nullable();

            // Confirmation
            $table->text('confirmation_message')->nullable();
            $table->text('confirmation_email_body')->nullable();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('published_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
