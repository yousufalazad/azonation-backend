<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Name of the category');
            $table->text('description')->nullable()->comment('A brief description of the category');
           
            $table->foreignId('business_type_id')->nullable()
                ->constrained('business_types')
                ->onDelete('cascade')
                ->comment('Relation with business_types');

            $table->string('slug')->unique()->comment('URL-friendly version of the category name');
            $table->string('meta_description')->nullable()->comment('SEO description for the category');
            $table->string('category_image_path')->nullable()->comment('Path to the category image');
            $table->integer('order')->default(0)->comment('Order of the category for display purposes');

            $table->boolean('is_active')->default(true)->comment('Whether the categories is active or inactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
