<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This creates the 'payment_logs' table to store records of payment history linked to invoices.
     */
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();

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

            // Payment gateway used for the transaction
            $table->string('gateway')->comment('Payment gateway used for the transaction (e.g., PayPal, Stripe, etc.)');

            // Transaction ID from the payment gateway
            $table->string('transaction_id')->nullable()->comment('Unique transaction ID from the payment gateway');

            // Status of the payment process
            $table->enum('payment_status', ['initiated', 'completed', 'failed', 'pending', 'refunded'])
                ->default('initiated')
                ->comment('The status of the payment: initiated, completed, failed, pending, or refunded');

            // Payment method used by the customer
            $table->enum('payment_method', ['card', 'bank_transfer', 'cash', 'mobile_payment', 'crypto'])
                ->default('card')
                ->comment('Payment method used for the transaction: card, bank transfer, cash, mobile payment, or cryptocurrency');

            // Currency used for the transaction
            $table->string('currency_code', 3)->comment('Currency code for the transaction (e.g., USD, GBP).');

            // Total amount paid in the transaction
            $table->decimal('amount_paid', 15, 2)->comment('Total amount paid in the transaction currency.');

            // Exchange rate at the time of transaction if applicable
            $table->decimal('exchange_rate', 10, 4)->nullable()->comment('Exchange rate at the time of transaction.');

            // Optional notes or comments regarding the payment
            $table->text('note')->nullable()->comment('Optional note regarding the payment.');

            // Admin comments for additional information or audits
            $table->string('admin_note', 255)->nullable()->comment('Internal note for admin.');


            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'payment_logs' table if needed.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
