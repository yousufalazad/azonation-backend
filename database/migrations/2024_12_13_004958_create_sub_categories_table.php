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
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique()->comment('Name of the subcategory');
            $table->text('description')->nullable()->comment('A brief description of the subcategory');
            
            $table->foreignId('category_id')  // Foreign key referencing the parent category (top-level category)
                ->constrained('categories')
                ->onDelete('cascade')
                ->comment('Foreign key: Links to the categories table (the parent category)');
            
                $table->string('slug')->unique()->comment('URL-friendly version of the subcategory name');
            $table->string('meta_description')->nullable()->comment('SEO description for the subcategory');
            $table->string('sub_category_image_path')->nullable()->comment('Path to the subcategory image');
            $table->integer('order')->default(0)->comment('Order of the subcategory for display purposes');


            $table->boolean('is_active')->default(true)->comment('Whether the sub_categories is active or inactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
