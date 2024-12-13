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
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->comment('Foreign key linking to the products table');
            $table->foreignId('attribute_id')
                ->constrained('attributes')
                ->cascadeOnDelete()
                ->comment('Foreign key linking to the attributes table');
            $table->foreignId('attribute_value_id')
                ->constrained('attribute_values')
                ->cascadeOnDelete()
                ->comment('Foreign key linking to the attribute values table');

            $table->boolean('is_active')->default(true)->comment('Whether the product_attributes is active or inactive');

            $table->timestamps();

            $table->unique(['product_id', 'attribute_id', 'attribute_value_id'], 'unique_product_attribute');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
