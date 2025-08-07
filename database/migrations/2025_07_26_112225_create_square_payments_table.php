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
        Schema::create('square_payments', function (Blueprint $table) {
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

            // Square transaction identifiers
            $table->string('customer_id')->nullable();
            $table->string('location_id')->nullable();

            // Payment details
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('status')->nullable(); // COMPLETED, FAILED, etc.
            $table->string('payment_method')->nullable(); // CARD, CASH, WALLET, etc.

            // Card details (if used)
            $table->string('card_brand')->nullable();
            $table->string('card_last_4')->nullable();

            // Customer info
            $table->string('payer_email')->nullable();
            $table->string('payer_name')->nullable();

            $table->string('receipt_url')->nullable(); // Receipt link
            $table->string('note')->nullable(); // Optional description or memo

            $table->timestamp('completed_at')->nullable(); // When payment completed

            // Refund info
            $table->string('refund_id')->nullable();
            $table->decimal('refunded_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();

            // Webhook tracking
            $table->string('webhook_event_type')->nullable();
            $table->timestamp('webhook_received_at')->nullable();

            $table->json('square_response')->nullable(); // Full raw API/webhook response
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('square_payments');
    }
};
