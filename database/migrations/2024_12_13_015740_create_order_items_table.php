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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete()
                ->comment('Foreign key: Links to the orders table');
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->comment('Foreign key: Links to the products table');

            $table->string('product_name')->comment('Name of the product at the time of order placement');
            $table->text('product_attributes')->nullable()->comment('JSON data containing the product attributes (e.g., colour, size, material)');
            $table->decimal('unit_price', 10, 2)->comment('Price of a single unit of the product');
            $table->integer('quantity')->comment('Quantity of the product purchased');
            $table->decimal('total_price', 10, 2)->comment('Total price for this product (unit price * quantity)');
            $table->decimal('discount_amount', 10, 2)->nullable()->comment('Discount applied to this item');
            $table->text('note')->nullable()->comment('Additional notes or comments regarding this item');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
