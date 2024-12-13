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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();


            $table->timestamps();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();  // Unique ID for the attribute
        
            // The attribute name (e.g., "Size", "Color", "Material")
            $table->string('name')->unique()->comment('The name of the attribute (e.g., Size, Color, Material)');
        
            // The attribute type, such as "select", "input", "checkbox", etc.
            $table->enum('type', ['select', 'input', 'checkbox', 'color'])->default('select')->comment('The type of attribute (select, input, checkbox, color)');
        
            // Stores the possible values for the attribute (e.g., S, M, L for size or Red, Blue for color)
            $table->json('possible_values')->nullable()->comment('Possible values for the attribute (JSON formatted array)');
            
            // Foreign Key linking to product categories, optional for future scalability (allows filtering of attributes based on category)
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->onDelete('set null')
                ->comment('Foreign key: Links to the category for this attribute (nullable)');
            
                $table->foreignId('sub_category_id')
                ->nullable()
                ->constrained('sub_categories')
                ->onDelete('set null')
                ->comment('Foreign key: Links to the category for this attribute (nullable)');

                $table->foreignId('sub_sub_category_id')
                ->nullable()
                ->constrained('sub_sub_categories')
                ->onDelete('set null')
                ->comment('Foreign key: Links to the category for this attribute (nullable)');

            $table->boolean('is_active')->default(true)->comment('Whether the attribute is active or inactive');

        
            $table->timestamps();  // Timestamps for created_at and updated_at
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
