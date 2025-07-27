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
        Schema::create('twocheckout_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')
                ->constrained('payments')
                ->onDelete('cascade')
                ->comment('Reference to the main payments table');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User who submitted the manual payment (nullable for anonymous)');

            $table->string('order_number')->index(); // 2Checkout Order Number
            $table->string('merchant_order_id')->nullable(); // Your internal order ID
            $table->string('invoice_id')->nullable(); // Optional invoice reference
            $table->string('status')->nullable(); // COMPLETE, AUTHRECEIVED, DECLINED, etc.

            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');

            $table->string('payment_method')->nullable(); // credit_card, PayPal, etc.
            $table->string('payment_type')->nullable(); // SALE, REFUND, TEST, etc.

            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_ip')->nullable();

            $table->string('custom_id')->nullable(); // Optional custom field
            $table->string('description')->nullable();

            $table->timestamp('payment_time')->nullable();

            // Refund info
            $table->string('refund_reference')->nullable();
            $table->string('refund_status')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();

            // Webhook event details
            $table->string('webhook_event_type')->nullable();
            $table->timestamp('webhook_received_at')->nullable();

            $table->json('twocheckout_response')->nullable(); // full response or webhook data

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('twocheckout_payments');
    }
};
