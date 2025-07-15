<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // Primary key

            // Unique identifier for the payment transaction
            $table->string('azonation_transaction_id')->nullable()
                ->comment('A unique identifier for tracking the payment transaction.');

            // Unique identifier for the payment gateway transaction
            $table->string('gateway_transaction_id')->nullable()
                ->comment('A unique identifier provided by the payment gateway for tracking the transaction.');

            // Foreign key to the 'invoices' table
            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained('invoices')
                ->onDelete('set null')
                ->comment('Foreign key linking to the invoices table, cascades on delete');

            // Foreign key linking to the 'users' table (payer)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->comment('User who made the payment, null if anonymous.');

            // Store user details statically for historical reference
            $table->string('user_name')
                ->nullable()
                ->comment('User name snapshot for billing reference');

            $table->decimal('paid_amount', 10, 2)
                ->comment('The amount of money paid in this transaction.');

            $table->dateTimeTz('paid_at')->nullable()
                ->comment('The date and time the payment was completed.');

            // Payment gateway used for the transaction
            $table->enum('gateway_type', ['stripe', 'paypal', 'sslcommerze', 'bkash', 'rocket', 'upi', 'alipay', 'applepay', 'gpay'])
                ->comment('The payment gateway used for the transaction, e.g., Stripe, PayPal.');

            //payment method
            $table->string('payment_method', 30)
                ->nullable()
                ->comment('The method of payment used, e.g., card, bank transfer, etc.');

            $table->string('gateway_response', 30)->nullable()
                ->comment('The payment gateway response, e.g., Stripe, PayPal.');

            $table->string('payment_reference')->nullable()
                ->comment('Reference ID provided by the payment gateway for this transaction (e.g., PayPal payment ID).');

            $table->enum('transaction_status', ['successful', 'Failed', 'pending', 'processing', 'cancelled', 'refunded', 'chargeback', 'disputed', 'error', 'unknown', 'in_progress', 'partially_refunded', 'partially_charged', 'partially_failed', 'partially_cancelled'])
                ->default('pending')
                ->comment('Current payment status of the transaction');

            //payer_country and payer_currency_rate
            $table->string('payer_country', 30)->nullable()
                ->comment('The country of the payer, full country name).');

            $table->decimal('payer_currency_rate', 10, 4)->nullable()
                ->comment('The exchange rate of the payer\'s currency against the payment currency at the time of the transaction.');

            $table->enum('payment_status', ['initiated', 'completed', 'failed', 'pending', 'refunded'])
                ->default('initiated')
                ->comment('The status of the payment: initiated, completed, failed, pending, or refunded');

            $table->decimal('payment_fee', 10, 2)->nullable()->comment('The fee charged by the payment gateway for processing the transaction.');
            $table->decimal('remaining_due', 10, 2)->comment('Remaining amount due');
            $table->string('currency_code', 3)->comment('Currency code for the payment transaction');
            $table->string('paid_currency', 3)
                ->comment('The currency of the payment (ISO 4217 code, e.g., USD, GBP).');

            $table->text('payment_note')->nullable()
                ->comment('Additional notes or information about the payment.');

            $table->softDeletes()
                ->comment('Allows for soft deletion of payment records, enabling them to be restored later if needed.');

            $table->boolean('is_active')->default(true)
                ->comment('Indicates whether the payment record is active or inactive (e.g., deactivated due to errors or disputes).');


            // Use Carbon's timestamp for paid_at column instead of date for better compatibility with different DBMS

            //$table->timestamps('paid_at');

            // $table->date('payment_date')
            //     ->comment('The date the payment was made.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
