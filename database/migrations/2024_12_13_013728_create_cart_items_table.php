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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')
                ->constrained('carts')
                ->cascadeOnDelete()
                ->comment('Foreign key: relations to carts table');

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->comment('Foreign key: relations to products table');

            $table->integer('quantity')->default(1)->comment('Quantity of the product in the cart');

            $table->decimal('price', 10, 2)->comment('Price of the product in the cart');

            $table->string('note')->nullable()->comment('Additional notes or comments regarding this item in the cart');

            $table->boolean('is_active')->default(true)->comment('Whether the cart_items is active or inactive');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
