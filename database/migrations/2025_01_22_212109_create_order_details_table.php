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
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
            ->constrained('orders')
            ->cascadeOnDelete()
            ->comment('Foreign key: Links to the orders table');

            $table->string('shipping_address')->comment('Shipping address for the order');
            $table->enum('shipping_status', ['pending', 'shipped', 'delivered'])->default('pending')->comment('Status of shipping');
            $table->enum('shipping_method', ['Standard Shipping', 'Express Shipping', 'Free Shipping'])->default('Standard Shipping')->nullable()->comment('Shipping method used for the order, e.g., Standard Shipping, Express Shipping');
            $table->text('shipping_note')->nullable()->comment('Additional details for the shipping');
            $table->text('customer_note')->nullable()->comment('Additional notes or instructions for the order');
            $table->string('admin_note')->nullable()->comment('Admin notes or instructions for the order from Azonation team');
             
            $table->string('tracking_number')->nullable()->comment('Tracking number for the order, if applicable');
            $table->timestamp('delivery_date_expected')->nullable()->comment('Date and time for expected delivery');
            $table->timestamp('delivery_date_actual')->nullable()->comment('Date and time for actual delivery');
            $table->enum('order_status', ['pending', 'processing', 'completed', 'cancelled', 'refunded', 'confirmed'])->default('pending')->comment('Order status');
            
            $table->timestamp('cancelled_at')->nullable()->comment('Date and time when the order was cancelled');
            $table->string('cancellation_reason')->nullable()->comment('Cancellation reason');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
