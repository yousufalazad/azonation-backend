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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->comment('Foreign key linking to the products table');
                
            $table->string('sku')->unique()->comment('Stock Keeping Unit for the variant');
            $table->decimal('price', 10, 2)->comment('The price of the variant');
            $table->integer('stock')->default(0)->comment('The stock quantity of the variant');
            $table->json('attributes')->comment('JSON representation of attributes for this variant');
            
            // OR

            // $table->foreignId('product_attribute_id')
            //     ->constrained('product_attributes')
            //     ->cascadeOnDelete()
            //     ->comment('Foreign key linking to the attributes for this variant');

            $table->boolean('is_active')->default(true)->comment('Whether the product_variant is active or inactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
