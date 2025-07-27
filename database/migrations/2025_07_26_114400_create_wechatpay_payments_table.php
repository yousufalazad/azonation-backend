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
        Schema::create('wechatpay_payments', function (Blueprint $table) {
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

            // Identifiers
            $table->string('wechat_transaction_id')->index(); // WeChat transaction ID
            $table->string('merchant_order_id')->nullable(); // Your system's order ID (out_trade_no)

            // Payer/app info
            $table->string('appid')->nullable(); // Your WeChat app ID
            $table->string('openid')->nullable(); // WeChat user ID (payer)

            // Payment details
            $table->string('trade_type')->nullable(); // JSAPI, NATIVE, APP, etc.
            $table->string('trade_state')->nullable(); // SUCCESS, NOTPAY, etc.
            $table->string('bank_type')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('payer_total', 10, 2)->nullable();
            $table->string('currency', 10)->default('CNY');
            $table->timestamp('success_time')->nullable();
            $table->string('payer_info')->nullable(); // Optional payer info (masked name, email, etc.)

            // Refund details
            $table->string('refund_id')->nullable();
            $table->string('refund_status')->nullable(); // SUCCESS, PROCESSING, CLOSED, etc.
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->timestamp('refund_time')->nullable();

            // Optional extras
            $table->json('promotion_detail')->nullable(); // Offers, coupons, etc.

            // Webhook support
            $table->string('webhook_event_type')->nullable();
            $table->timestamp('webhook_received_at')->nullable();

            // Raw full response
            $table->json('wechatpay_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wechatpay_payments');
    }
};
