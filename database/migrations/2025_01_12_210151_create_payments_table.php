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
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // Primary key

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

            $table->decimal('payment_amount', 10, 2)
                ->comment('The amount of money paid in this transaction.');

            $table->string('payment_method')->nullable()
                ->comment('The method used for payment, e.g., Credit Card, PayPal, or Bank Transfer.');

            $table->date('payment_date')
                ->comment('The date the payment was made.');

            $table->string('transaction_id')->nullable()
                ->comment('A unique identifier provided by the payment gateway for tracking the transaction.');

            $table->string('payment_gateway', 30)->nullable()
                ->comment('The payment gateway used for the transaction, e.g., Stripe, PayPal.');

                $table->string('gateway_response', 30)->nullable()
                ->comment('The payment gateway response, e.g., Stripe, PayPal.');


            $table->enum('payment_status', ['initiated', 'completed', 'failed', 'pending', 'refunded'])
                ->default('initiated')
                ->comment('The status of the payment: initiated, completed, failed, pending, or refunded');

            $table->string('payment_currency', 3)
                ->comment('The currency of the payment (ISO 4217 code, e.g., USD, GBP).');

            $table->decimal('payment_fee', 10, 2)->nullable()->comment('The fee charged by the payment gateway for processing the transaction.');
            $table->decimal('amount_paid', 10, 2)->comment('The total amount received');
            $table->decimal('total_due', 10, 2)->comment('Remaining amount due');
            $table->string('currency', 3)->comment('Currency code for the order amount');
            
            $table->enum('transaction_status', ['successful', 'Failed', 'pending', 'processing'])
                ->default('pending')
                ->comment('Current payment status of the transaction');

            $table->timestamps('paid_at')->comment('Date and time when the payment was made.');

            $table->string('payment_reference_id')->nullable()
                ->comment('Reference ID provided by the payment gateway for this transaction (e.g., PayPal payment ID).');

            $table->text('payment_note')->nullable()
                ->comment('Additional notes or information about the payment.');

            $table->softDeletes()
                ->comment('Allows for soft deletion of payment records, enabling them to be restored later if needed.');

            $table->boolean('is_active')->default(true)
                ->comment('Indicates whether the payment record is active or inactive (e.g., deactivated due to errors or disputes).');

            $table->timestamps(); // Created at and Updated at
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
