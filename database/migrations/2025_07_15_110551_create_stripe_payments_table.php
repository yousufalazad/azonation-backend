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
         Schema::create('stripe_payments', function (Blueprint $table) {
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

            $table->string('payment_intent_id');
            $table->string('charge_id')->nullable();
            $table->string('customer_id');
            $table->string('subscription_id')->nullable();
            $table->string('invoice_id')->nullable();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 10);
            $table->decimal('payment_fee', 10, 2)->nullable()->comment('The fee charged by the payment gateway for processing the transaction.');

            $table->string('payment_method');
            $table->string('payment_method_type');

            $table->string('payment_status');
            $table->string('receipt_url');

            $table->string('description')->nullable();
            $table->string('statement_descriptor')->nullable();

            $table->string('card_brand')->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->string('card_exp_month', 2)->nullable();
            $table->string('card_exp_year', 4)->nullable();

            $table->boolean('refunded')->default(false);
            $table->string('refund_id')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();

            $table->string('dispute_id')->nullable();

            $table->string('status_message')->nullable();
            $table->json('stripe_response')->nullable();

            $table->timestamp('webhook_received_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_payments');
    }
};

