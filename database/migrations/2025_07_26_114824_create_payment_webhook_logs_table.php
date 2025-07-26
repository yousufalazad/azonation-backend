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
            // Optional linkage to the payment
            $table->unsignedBigInteger('payment_id')->nullable()->index();

            // General info
            $table->string('gateway_name'); // e.g., stripe, paypal, sslcommerz
            $table->string('event_type')->nullable(); // e.g., payment_intent.succeeded, PAYMENT.SALE.COMPLETED

            // Webhook request context
            $table->timestamp('received_at')->useCurrent();
            $table->string('ip_address')->nullable(); // IP from where webhook came
            $table->string('user_agent')->nullable(); // Optional

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
