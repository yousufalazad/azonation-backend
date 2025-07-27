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
        Schema::create('tap_payments', function (Blueprint $table) {
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

            $table->string('tap_id')->index()->comment('Tap Charge ID (e.g., chg_xxx)');
            $table->string('merchant_id')->nullable()->comment('Tap merchant ID');
            $table->string('customer_id')->nullable()->comment('Tap customer ID (if customer was created)');

            $table->string('status')->nullable()->comment('Payment status: INITIATED, CAPTURED, FAILED, CANCELLED');
            $table->decimal('amount', 10, 2)->default(0)->comment('Charged amount');
            $table->string('currency', 10)->default('SAR')->comment('Currency used (e.g., SAR, KWD, AED)');

            $table->string('payment_method')->nullable()->comment('mada, apple_pay, credit_card, etc.');
            $table->string('card_type')->nullable()->comment('Card type, e.g., credit, debit');
            $table->string('card_brand')->nullable()->comment('Card brand, e.g., VISA, MASTERCARD');
            $table->string('card_last_four')->nullable()->comment('Masked card number (last 4 digits)');

            $table->string('receipt_url')->nullable()->comment('Tap-hosted payment receipt URL');
            $table->string('payer_name')->nullable()->comment('Name of payer');
            $table->string('payer_email')->nullable()->comment('Email of payer');

            $table->timestamp('payment_time')->nullable()->comment('Time when payment was captured');

            // Refund info
            $table->string('refund_id')->nullable()->comment('Tap refund ID if refunded');
            $table->decimal('refund_amount', 10, 2)->nullable()->comment('Amount refunded');
            $table->string('refund_reason')->nullable()->comment('Reason for refund');
            $table->timestamp('refund_time')->nullable()->comment('Time refund was processed');

            $table->json('tap_response')->nullable()->comment('Full Tap API response or webhook payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tap_payments');
    }
};
