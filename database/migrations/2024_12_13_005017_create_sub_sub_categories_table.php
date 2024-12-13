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
        Schema::create('sub_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Name of the sub_sub_categories');
            $table->text('description')->nullable()->comment('A brief description of the sub_sub_categories');
            
            $table->foreignId('sub_category_id')  // Foreign key referencing the parent category (top-level category)
                ->constrained('sub_categories')
                ->onDelete('cascade')
                ->comment('Foreign key: Links to the sub_categories table (the parent category)');
            
                $table->string('slug')->unique()->comment('URL-friendly version of the sub_sub_categories name');
            $table->string('meta_description')->nullable()->comment('SEO description for the sub_sub_categories');
            $table->string('sub_sub_category_image_path')->nullable()->comment('Path to the sub_sub_categories image');
            $table->integer('order')->default(0)->comment('Order of the sub_sub_categories for display purposes');

            $table->boolean('is_active')->default(true)->comment('Whether the sub_sub_categories is active or inactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_sub_categories');
    }
};
