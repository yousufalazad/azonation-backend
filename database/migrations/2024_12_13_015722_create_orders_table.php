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
            $table->string('payment_gateway')->nullable()->comment('Payment gateway used for the order, e.g., PayPal, Stripe');
            $table->string('coupon_code')->nullable()->comment('Coupon code used for the order, if applicable');
            $table->string('shipping_method')->nullable()->comment('Shipping method used for the order, e.g., Standard Shipping, Express Shipping');
            $table->text('shipping_note')->nullable()->comment('Additional details for the shipping');
            $table->text('customer_note')->nullable()->comment('Additional notes or instructions for the order');
            $table->string('admin_note')->nullable()->comment('Admin notes or instructions for the order from Azonation team');
            $table->string('invoice_number')->nullable()->comment('Invoice number for the order, if applicable');
            $table->string('tracking_number')->nullable()->comment('Tracking number for the order, if applicable');
            $table->timestamp('order_date')->nullable()->comment('Date and time when the order was placed');
            $table->timestamp('delivery_date_expected')->nullable()->comment('Date and time for expected delivery');
            $table->timestamp('delivery_date_actual')->nullable()->comment('Date and time for actual delivery');
            $table->string('payment_method')->nullable()->comment('Payment method used for the order, e.g., PayPal, Credit Card');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->comment('Payment status of the order');
            $table->string('payment_status_reason')->nullable()->comment('Reason for the current payment status');
            $table->string('payment_reference')->nullable()->comment('Payment method reference number, if applicable');
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'refunded'])->default('pending')->comment('Order status');
            $table->enum('payment_type', ['online', 'offline', 'cod', 'credit_card', 'paypal', 'stripe', 'other'])->default('online')->comment('Payment type for the order');
            
            //Note for invoice not for order
            // $table->string('payment_gateway_transaction_id')->nullable()->comment('Transaction ID from the payment gateway');
            // $table->decimal('payment_gateway_fee', 10, 2)->nullable()->comment('Payment gateway fee for the order');
            // $table->decimal('payment_gateway_refund_amount', 10, 2)->nullable()->comment('Refund amount from the payment gateway for the order');
            // $table->decimal('payment_gateway_fee_currency', 3)->nullable()->comment('Currency code for the payment gateway fee');
            // $table->decimal('payment_gateway_refund_amount_currency', 3)->nullable()->comment('Currency code for the refund amount from the payment gateway');
            // $table->string('payment_gateway_refund_reason')->nullable()->comment('Reason for the refund from the payment gateway');
            // $table->string('payment_gateway_refund_transaction_id')->nullable()->comment('Refund transaction ID from the payment gateway');
            // $table->string('payment_gateway_refund_currency', 3)->nullable()->comment('Currency code for the refund transaction ID from the payment gateway');
            // $table->string('payment_gateway_refund_status')->nullable()->comment('Refund status from the payment gateway');
            // $table->string('payment_gateway_refund_date')->nullable()->comment('Date and time when the refund was initiated');
            // $table->string('payment_gateway_refund_admin_note')->nullable()->comment('Refund admin note for the order');
            // $table->string('payment_gateway_refund_reason_code')->nullable()->comment('Refund reason code from the payment gateway');
            // $table->string('payment_gateway_refund_reason_description')->nullable()->comment('Refund reason description from the payment gateway');
            // $table->string('payment_gateway_refund_request_id')->nullable()->comment('Refund request ID from the payment gateway');
            // $table->string('payment_gateway_refund_request_currency')->nullable()->comment('Currency code for the refund request ID from the payment gateway');
            // $table->string('payment_gateway_refund_request_status')->nullable()->comment('Refund request status from the payment gateway');
            // $table->string('payment_gateway_refund_request_date')->nullable()->comment('Date and time when the refund request was initiated');
            // $table->string('payment_gateway_refund_request_admin_note')->nullable()->comment('Refund request admin note for the order');
            // $table->string('payment_gateway_refund_request_reason_code')->nullable()->comment('Refund request reason code from the payment gateway');
            // $table->string('payment_gateway_refund_request_reason_description')->nullable()->comment('Refund request reason description from the payment gateway');

            //Note for tracking not for order
            // $table->timestamp('order_date')->comment('Date and time when the order was placed');
            // $table->timestamp('expected_delivery_date')->nullable()->comment('Expected delivery date for the order');
            // $table->timestamp('paid_date')->nullable()->comment('Date and time when the order was paid');
            // $table->timestamp('cancelled_date')->nullable()->comment('Date and time when the order was cancelled');
            // $table->timestamp('refunded_date')->nullable()->comment('Date and time when the order was refunded');
            // $table->timestamp('return_date')->nullable()->comment('Date and time when the order was returned');
            // $table->timestamp('return_request_date')->nullable()->comment('Date and time when the order return request was submitted');
            // $table->timestamp('return_accepted_date')->nullable()->comment('Date and time when the order return request was accepted by the customer');
            // $table->timestamp('return_rejected_date')->nullable()->comment('Date and time when the order return request was rejected by the customer');
            // $table->timestamp('return_sent_date')->nullable()->comment('Date and time when the order return request was sent to the customer');
            // $table->timestamp('return_received_date')->nullable()->comment('Date and time when the order return was received by the customer');
            // $table->timestamp('return_canceled_date')->nullable()->comment('Date and time when the order return was canceled by the customer');
            // $table->timestamp('return_returned_date')->nullable()->comment('Date and time when the order return was returned by the customer');
            // $table->timestamp('return_delayed_date')->nullable()->comment('Date and time when the order return was delayed');
            // $table->timestamp('return_refund_date')->nullable()->comment('Date and time when the order return refund was processed');
            // $table->timestamp('return_delayed_refund_date')->nullable()->comment('Date and time when the delayed order return refund was processed');
            // $table->timestamp('return_delayed_refund_canceled_date')->nullable()->comment('Date and time when the delayed order return refund was canceled');
            
            $table->timestamps(); // Default timestamps: created_at and updated_at


            $table->timestamps();
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
