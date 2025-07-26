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
        Schema::create('alipay_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // Optional for guest checkout

            // Identifiers
            $table->string('alipay_trade_no')->index(); // Alipay transaction ID
            $table->string('merchant_order_id')->nullable(); // Your order ID (out_trade_no)

            // Buyer info
            $table->string('buyer_logon_id')->nullable(); // Masked email/phone
            $table->string('buyer_user_id')->nullable(); // Alipay user ID

            // Payment info
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('CNY');
            $table->decimal('receipt_amount', 10, 2)->nullable(); // Actual amount received
            $table->string('payment_status')->nullable(); // TRADE_SUCCESS, etc.
            $table->string('payment_method')->nullable(); // alipay, card, app, etc.
            $table->string('product_code')->nullable(); // FAST_INSTANT_TRADE_PAY, etc.
            $table->timestamp('pay_time')->nullable();

            // Refund details
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->timestamp('refund_time')->nullable();
            $table->string('refund_status')->nullable(); // REFUND_SUCCESS, etc.

            $table->string('fund_channel')->nullable(); // credit_card, balance, etc.

            // Webhook support
            $table->string('webhook_event_type')->nullable();
            $table->timestamp('webhook_received_at')->nullable();

            $table->json('alipay_response')->nullable(); // Full response or notification payload

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alipay_payments');
    }
};
