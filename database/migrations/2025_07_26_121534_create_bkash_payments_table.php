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
        Schema::create('bkash_payments', function (Blueprint $table) {
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

            $table->string('trx_id')->nullable()->comment('bKash Transaction ID (trxID)');
            $table->string('merchant_invoice_number')->nullable()->comment('Merchant-provided invoice or order ID');

            $table->decimal('amount', 10, 2)->default(0)->comment('Payment amount');
            $table->string('currency', 10)->default('BDT')->comment('Payment currency, e.g., BDT');
            $table->string('status')->nullable()->comment('Payment status: Completed, Pending, Failed, Cancelled, etc.');
            $table->string('intent')->nullable()->comment('Payment intent: sale or authorize');
            $table->string('payment_method')->nullable()->comment('Payment method, e.g., bkash');
            $table->string('transaction_type')->nullable()->comment('Transaction type: checkout, disbursement, etc.');

            $table->timestamp('payment_create_time')->nullable()->comment('Timestamp when payment was initiated');
            $table->timestamp('payment_execute_time')->nullable()->comment('Timestamp when payment was executed');

            $table->string('payer_reference')->nullable()->comment('Masked mobile number or reference of the payer');

            $table->string('refund_trx_id')->nullable()->comment('Transaction ID for refund (if refunded)');
            $table->timestamp('refund_time')->nullable()->comment('Timestamp when refund was processed');
            $table->decimal('refund_amount', 10, 2)->nullable()->comment('Refunded amount');
            $table->string('refund_reason')->nullable()->comment('Reason for refund');

            $table->json('bkash_response')->nullable()->comment('Raw bKash API or webhook response');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bkash_payments');
    }
};
