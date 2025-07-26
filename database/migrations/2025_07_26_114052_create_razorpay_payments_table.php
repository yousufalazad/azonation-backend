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
        Schema::create('razorpay_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // Nullable for guest checkout

            // Razorpay identifiers
            $table->string('razorpay_payment_id')->index();
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_signature')->nullable();

            // Payment details
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('INR');
            $table->string('status')->nullable(); // captured, failed, authorized, etc.
            $table->string('method')->nullable(); // card, upi, netbanking, etc.
            $table->string('description')->nullable();

            // Payer info
            $table->string('email')->nullable();
            $table->string('contact')->nullable();

            // Payment method-specific fields
            $table->string('card_id')->nullable();
            $table->string('bank')->nullable();
            $table->string('wallet')->nullable();
            $table->string('vpa')->nullable();

            // Fees and time
            $table->decimal('fee', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->timestamp('captured_at')->nullable();

            // Refund info
            $table->string('refund_id')->nullable();
            $table->string('refund_status')->nullable(); // processed, failed, etc.
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();

            // Webhook tracking
            $table->string('webhook_event_type')->nullable();
            $table->timestamp('webhook_received_at')->nullable();

            // Full response
            $table->json('razorpay_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razorpay_payments');
    }
};
