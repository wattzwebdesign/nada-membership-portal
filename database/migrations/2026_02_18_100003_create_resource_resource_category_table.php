<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_resource_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_category_id')->constrained()->cascadeOnDelete();
            $table->unique(['resource_id', 'resource_category_id'], 'resource_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_resource_category');
    }
};
