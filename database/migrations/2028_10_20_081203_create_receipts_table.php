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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();

            // Unique receipt identifier (e.g., "AZON-RCT-123456789")
            $table->string('receipt_code', 15)
                ->unique()
                ->comment('Unique alphanumeric code for the receipt.');

            // Foreign key linking to the 'invoices' table
            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->onDelete('cascade')
                ->comment('References the invoice for which the payment was made.');

            // Foreign key linking to the 'users' table
            $table->foreignId('user_id')
            ->nullable()
            ->constrained()
            ->onDelete('set null')
            ->comment('The user who made the payment.');
            

            // Amount received
            $table->decimal('amount_received', 15, 2)
                ->comment('The total amount received.');

            $table->string('currency_code', 3)->comment('Currency code for the receipt.');
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           
            // Payment method details (e.g., credit card, bank transfer, PayPal)
            $table->enum('gateway_type', ['stripe', 'paypal', 'sslcommerze', 'bkash', 'rocket', 'upi', 'alipay', 'applepay', 'gpay'])
                ->comment('The payment gateway used for the transaction, e.g., Stripe, PayPal.');

            //payer_country and payer_currency_rate
            $table->string('payer_country', 30)->nullable()
                ->comment('The country of the payer, full country name).');

            $table->decimal('payer_currency_rate', 10, 4)->nullable()
                ->comment('The exchange rate of the payer\'s currency against the payment currency at the time of the transaction.');
                

            // Transaction reference from payment gateway, if available
            $table->string('transaction_reference', 50)
                ->nullable()
                ->comment('Unique reference from payment gateway.');

            // Date the payment was received
            $table->date('payment_date')
                ->comment('Date when the payment was received.');

            // Optional notes for additional information
            $table->string('note', 255)
                ->nullable()
                ->comment('Optional notes related to the payment.');

            // Status of the receipt (e.g., processed, refunded, etc.)
            $table->enum('status', ['processed', 'refunded', 'pending'])
                ->default('processed')
                ->comment('Current status of the receipt.');

            // Hidden admin note for internal comments
            $table->string('admin_note', 255)
                ->nullable()
                ->comment('Hidden admin note for internal purposes.');

            // Publishing status
            $table->boolean('is_published')->default(false)->comment('Indicates if the invoice is published');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
