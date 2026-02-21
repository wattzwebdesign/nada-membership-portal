<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('glossary_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('glossary_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('glossary_category_id')->constrained()->cascadeOnDelete();
            $table->string('term');
            $table->string('slug')->unique();
            $table->text('definition');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glossary_terms');
        Schema::dropIfExists('glossary_categories');
    }
};
