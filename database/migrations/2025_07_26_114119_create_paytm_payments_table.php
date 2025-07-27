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
        Schema::create('paytm_payments', function (Blueprint $table) {
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
            $table->string('order_id')->index(); // Your system's Order ID
            $table->string('txn_id')->nullable(); // Paytm transaction ID

            // Payment details
            $table->decimal('txn_amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('INR');
            $table->string('status')->nullable(); // TXN_SUCCESS, TXN_FAILURE, etc.

            $table->string('gateway_name')->nullable(); // Gateway (e.g., PAYTM)
            $table->string('bank_name')->nullable(); // Issuing bank
            $table->string('payment_mode')->nullable(); // CC, NB, UPI, WALLET, etc.

            $table->string('resp_code')->nullable(); // e.g., 01, 227
            $table->string('resp_msg')->nullable(); // Message from Paytm
            $table->timestamp('txn_date')->nullable(); // Time of transaction

            // Refund info
            $table->string('refund_id')->nullable();
            $table->string('refund_status')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();

            // Webhook support
            $table->string('webhook_event_type')->nullable();
            $table->timestamp('webhook_received_at')->nullable();

            // Full JSON response from Paytm
            $table->json('paytm_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paytm_payments');
    }
};
