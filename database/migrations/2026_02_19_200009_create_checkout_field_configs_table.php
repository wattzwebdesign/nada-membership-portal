<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_field_configs', function (Blueprint $table) {
            $table->id();
            $table->string('field_name')->unique();
            $table->string('label');
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('section');
            $table->timestamps();
        });

        // Seed default checkout fields
        $fields = [
            ['field_name' => 'customer_first_name', 'label' => 'First Name', 'is_visible' => true, 'is_required' => true, 'sort_order' => 1, 'section' => 'customer'],
            ['field_name' => 'customer_last_name', 'label' => 'Last Name', 'is_visible' => true, 'is_required' => true, 'sort_order' => 2, 'section' => 'customer'],
            ['field_name' => 'customer_email', 'label' => 'Email', 'is_visible' => true, 'is_required' => true, 'sort_order' => 3, 'section' => 'customer'],
            ['field_name' => 'customer_phone', 'label' => 'Phone', 'is_visible' => true, 'is_required' => false, 'sort_order' => 4, 'section' => 'customer'],
            ['field_name' => 'customer_company', 'label' => 'Company', 'is_visible' => true, 'is_required' => false, 'sort_order' => 5, 'section' => 'customer'],
            ['field_name' => 'billing_address_line_1', 'label' => 'Billing Address', 'is_visible' => true, 'is_required' => false, 'sort_order' => 1, 'section' => 'billing'],
            ['field_name' => 'billing_address_line_2', 'label' => 'Billing Address Line 2', 'is_visible' => true, 'is_required' => false, 'sort_order' => 2, 'section' => 'billing'],
            ['field_name' => 'billing_city', 'label' => 'Billing City', 'is_visible' => true, 'is_required' => false, 'sort_order' => 3, 'section' => 'billing'],
            ['field_name' => 'billing_state', 'label' => 'Billing State', 'is_visible' => true, 'is_required' => false, 'sort_order' => 4, 'section' => 'billing'],
            ['field_name' => 'billing_zip', 'label' => 'Billing ZIP', 'is_visible' => true, 'is_required' => false, 'sort_order' => 5, 'section' => 'billing'],
            ['field_name' => 'billing_country', 'label' => 'Billing Country', 'is_visible' => true, 'is_required' => false, 'sort_order' => 6, 'section' => 'billing'],
            ['field_name' => 'shipping_address_line_1', 'label' => 'Shipping Address', 'is_visible' => true, 'is_required' => false, 'sort_order' => 1, 'section' => 'shipping'],
            ['field_name' => 'shipping_address_line_2', 'label' => 'Shipping Address Line 2', 'is_visible' => true, 'is_required' => false, 'sort_order' => 2, 'section' => 'shipping'],
            ['field_name' => 'shipping_city', 'label' => 'Shipping City', 'is_visible' => true, 'is_required' => false, 'sort_order' => 3, 'section' => 'shipping'],
            ['field_name' => 'shipping_state', 'label' => 'Shipping State', 'is_visible' => true, 'is_required' => false, 'sort_order' => 4, 'section' => 'shipping'],
            ['field_name' => 'shipping_zip', 'label' => 'Shipping ZIP', 'is_visible' => true, 'is_required' => false, 'sort_order' => 5, 'section' => 'shipping'],
            ['field_name' => 'shipping_country', 'label' => 'Shipping Country', 'is_visible' => true, 'is_required' => false, 'sort_order' => 6, 'section' => 'shipping'],
        ];

        foreach ($fields as $field) {
            \App\Models\CheckoutFieldConfig::create($field);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_field_configs');
    }
};
