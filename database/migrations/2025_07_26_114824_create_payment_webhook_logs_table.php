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
        Schema::create('payment_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')
                ->constrained('payments')
                ->onDelete('cascade')
                ->comment('Reference to the main payments table');

            

            // General info
            $table->string('gateway_name'); // e.g., stripe, paypal, sslcommerz
            $table->string('event_type')->nullable(); // e.g., payment_intent.succeeded, PAYMENT.SALE.COMPLETED

            // Webhook request context
            $table->timestamp('received_at')->useCurrent();
            $table->string('ip_address')->nullable(); // IP from where webhook came
            $table->string('user_agent')->nullable(); // Optional
            $table->boolean('is_chargeback')->default(false)
                ->comment('Indicates if the payment has been charged back by the payer.');

            $table->string('webhook_id')->nullable()
                ->comment('A unique identifier for the webhook event, if provided by the gateway.');
            $table->string('payer_country')->nullable();

            $table->string('payer_name')->nullable()
                ->comment('Name of the person who made the payment, if available.');
            $table->string('payer_email')->nullable()
                ->comment('Email of the person who made the payment, if available.');

            // Processing status
            $table->string('status')->default('pending'); // pending, processed, failed, skipped
            $table->text('error_message')->nullable(); // If failed

            // Full payload and headers
            $table->json('payload')->nullable();
            $table->json('headers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_logs');
    }
};
