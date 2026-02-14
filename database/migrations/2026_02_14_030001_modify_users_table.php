<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename 'name' to 'first_name' and add 'last_name'
            $table->renameColumn('name', 'first_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->after('first_name');
            $table->string('phone', 50)->nullable()->after('email_verified_at');
            $table->string('address_line_1')->nullable()->after('phone');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('city')->nullable()->after('address_line_2');
            $table->string('state')->nullable()->after('city');
            $table->string('zip', 20)->nullable()->after('state');
            $table->string('country', 2)->default('US')->after('zip');

            // Stripe
            $table->string('stripe_customer_id')->nullable()->index()->after('country');

            // Discount
            $table->string('discount_type')->default('none')->after('stripe_customer_id');
            $table->boolean('discount_approved')->default(false)->after('discount_type');
            $table->timestamp('discount_approved_at')->nullable()->after('discount_approved');
            $table->foreignId('discount_approved_by')->nullable()->constrained('users')->nullOnDelete()->after('discount_approved_at');

            // Trainer
            $table->string('trainer_application_status')->default('none')->after('discount_approved_by');
            $table->timestamp('trainer_approved_at')->nullable()->after('trainer_application_status');
            $table->foreignId('trainer_approved_by')->nullable()->constrained('users')->nullOnDelete()->after('trainer_approved_at');

            // Profile
            $table->string('profile_photo_path')->nullable()->after('trainer_approved_by');

            // Soft deletes
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'last_name', 'phone', 'address_line_1', 'address_line_2',
                'city', 'state', 'zip', 'country', 'stripe_customer_id',
                'discount_type', 'discount_approved', 'discount_approved_at',
                'discount_approved_by', 'trainer_application_status',
                'trainer_approved_at', 'trainer_approved_by', 'profile_photo_path',
            ]);
            $table->renameColumn('first_name', 'name');
        });
    }
};
