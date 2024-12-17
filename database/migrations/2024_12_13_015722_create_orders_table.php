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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Foreign key: The user who placed the order');
            
            $table->string('user_name')->nullable()->comment('Name of the customer placing the order');
            $table->string('order_number')->unique()->comment('Unique order identifier');
            $table->decimal('total_amount', 10, 2)->comment('Total amount for the order');
            $table->decimal('discount_amount', 10, 2)->nullable()->comment('Discount applied to the order, if any');
            $table->decimal('shipping_cost', 10, 2)->nullable()->comment('Cost of shipping');
            $table->decimal('total_tax', 10, 2)->nullable()->comment('Total tax applied to the order');
            $table->string('currency', 3)->default('USD')->comment('Currency code for the order amount');
            $table->enum('shipping_status', ['pending', 'shipped', 'delivered'])->default('pending')->comment('Status of shipping');
            $table->string('shipping_address')->comment('Shipping address for the order');
            $table->string('billing_address')->comment('Billing address for the order');
            $table->string('coupon_code')->nullable()->comment('Coupon code used for the order, if applicable');
            
            $table->enum('shipping_method', ['Standard Shipping', 'Express Shipping', 'Free Shipping'])->default('Standard Shipping')->nullable()->comment('Shipping method used for the order, e.g., Standard Shipping, Express Shipping');
            $table->text('shipping_note')->nullable()->comment('Additional details for the shipping');
            $table->text('customer_note')->nullable()->comment('Additional notes or instructions for the order');
            $table->string('admin_note')->nullable()->comment('Admin notes or instructions for the order from Azonation team');
            
            $table->string('tracking_number')->nullable()->comment('Tracking number for the order, if applicable');
            $table->timestamp('order_date')->nullable()->comment('Date and time when the order was placed');
            $table->timestamp('delivery_date_expected')->nullable()->comment('Date and time for expected delivery');
            $table->timestamp('delivery_date_actual')->nullable()->comment('Date and time for actual delivery');
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'refunded'])->default('pending')->comment('Order status');
            
            $table->timestamp('cancelled_at')->nullable()->comment('Date and time when the order was cancelled');
            
            $table->boolean('is_active')->default(true)->comment('Whether the product_variant is active or inactive');

            $table->timestamps(); // Default timestamps: created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
