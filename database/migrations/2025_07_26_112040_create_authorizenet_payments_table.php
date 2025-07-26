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
        Schema::create('authorizenet_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // nullable for guest

            // Transaction identifiers
            $table->string('transaction_id')->index(); // Authorize.Net transId
            $table->string('invoice_number')->nullable(); // Your internal invoice
            $table->string('description')->nullable();

            // Payment details
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');

            $table->string('payment_method')->nullable(); // credit_card, echeck, etc.
            $table->string('account_type')->nullable(); // Visa, MasterCard
            $table->string('account_number')->nullable(); // Masked card number
            $table->string('auth_code')->nullable(); // Authorization code

            // Customer info
            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();

            // Transaction status
            $table->string('status')->nullable(); // settledSuccessfully, declined, etc.
            $table->string('response_code')->nullable(); // 1=Approved, 2=Declined...
            $table->string('response_reason')->nullable();

            $table->timestamp('payment_time')->nullable(); // submitTimeUTC

            // Refund info
            $table->string('refund_transaction_id')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();

            // Dispute info
            $table->string('dispute_id')->nullable();

            // Webhook
            $table->string('webhook_event_type')->nullable();
            $table->timestamp('webhook_received_at')->nullable();

            $table->json('anet_response')->nullable(); // Full response from Authorize.Net

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorizenet_payments');
    }
};
