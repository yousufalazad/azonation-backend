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
        Schema::create('ssl_commerz_payments', function (Blueprint $table) {
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

            $table->string('payer_name')
                ->nullable()
                ->comment('Person name who make the payment');

            $table->string('payer_email')->nullable()->comment('Person email address who make the payment');

            $table->string('tran_id')->index(); // Merchant-generated transaction ID
            $table->string('val_id')->nullable(); // SSLCommerz validation ID

            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('payment_fee', 10, 2)->nullable();
            $table->string('currency', 10)->default('BDT');
            $table->string('payer_country', 30)->nullable()
                ->comment('The country of the payer, full country name).');
            $table->string('store_id')->nullable();
            $table->string('status')->nullable(); // Pending, Processing, Failed, etc.

            // Card details (masked & descriptive)
            $table->string('card_type')->nullable(); // Visa, MasterCard, etc.
            $table->string('card_no')->nullable(); // Masked card number
            $table->string('bank_tran_id')->nullable(); // Bank transaction reference

            $table->string('card_issuer')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_issuer_country')->nullable();
            $table->string('card_issuer_country_code', 5)->nullable();

            $table->decimal('currency_amount', 10, 2)->nullable(); // Converted amount
            $table->decimal('currency_rate', 10, 4)->nullable(); // Exchange rate

            $table->json('gateway_response')->nullable(); // Full response from SSLCommerz

            $table->tinyInteger('risk_level')->nullable();
            $table->string('risk_title')->nullable();

            $table->string('APIConnect')->nullable(); // Response from API (e.g., DONE)
            $table->timestamp('validated_on')->nullable(); // Only if validated

            $table->text('failed_reason')->nullable(); // If failure occurs

            $table->timestamp('ipn_received_at')->nullable(); // When IPN was received
            $table->timestamp('payment_date')->nullable(); // Time of payment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ssl_commerz_payments');
    }
};
