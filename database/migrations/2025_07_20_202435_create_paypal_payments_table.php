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
        Schema::create('paypal_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('paypal_order_id')->index();
            $table->string('paypal_payment_id')->nullable();
            $table->string('paypal_capture_id')->nullable();
            $table->string('paypal_payer_id')->nullable();
            $table->string('payer_email')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('status')->nullable(); // COMPLETED, PENDING, FAILED, etc.
            $table->string('payment_method')->nullable(); // paypal, credit_card, etc.
            $table->string('intent')->nullable(); // CAPTURE or AUTHORIZE
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('description')->nullable();
            $table->string('invoice_id')->nullable();
            $table->string('custom_id')->nullable();
            $table->timestamp('payment_time')->nullable();

            // Refund-related columns
            $table->string('refund_id')->nullable();
            $table->string('refund_status')->nullable(); // COMPLETED, PENDING, etc.
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();

            // Dispute info
            $table->string('dispute_id')->nullable();

            // Webhook tracking
            $table->string('webhook_event_type')->nullable();
            $table->timestamp('webhook_received_at')->nullable();

            // Full response
            $table->json('paypal_response')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paypal_payments');
    }
};
