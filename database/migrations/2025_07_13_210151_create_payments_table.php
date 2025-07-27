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
            $table->id();

            // Unique identifier for the payment transaction
            $table->string('azon_payment_txn_id')->unique()->nullable()
                ->comment('A unique identifier for tracking the payment transaction.');

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

            // User / Payer info
            $table->string('user_name')
                ->nullable()
                ->comment('User name / org name snapshot for payment reference');

            $table->string('user_email')->nullable()->comment('User/org email snapshot for payment reference');

            // Payment gateway used for the transaction
            $table->foreignId('payment_gateway_id')
                ->nullable()
                ->constrained('payment_gateways')
                ->onDelete('set null')
                ->comment('References the payment_gateways table');

            // Unique identifier for the payment gateway transaction, For webhook correlation
            $table->string('gateway_reference_id')->nullable()->index()
                ->comment('Gateway-specific reference or transaction ID.');

            //payment method
            $table->string('payment_method', 50)
                ->nullable()
                ->comment('The method of payment used, e.g., card, bank transfer, etc.');

            $table->enum('source_type', ['gateway', 'manual', 'admin'])
                ->default('gateway')
                ->comment('Source of this payment: gateway, manual, or admin override.');

            $table->enum('transaction_status', ['successful', 'failed', 'pending', 'processing', 'cancelled', 'refunded', 'chargeback', 'disputed', 'error', 'unknown', 'partially_refunded', 'partially_charged', 'partially_failed'])
                ->default('pending')
                ->comment('Current payment status of the transaction');

            $table->decimal('amount', 15, 2)->default(0)
                ->comment('The amount of this transaction.');

            $table->string('currency', 10)->comment('Currency code or symbol, e.g., USD, EUR, BDT');

            $table->dateTimeTz('payment_time')->nullable()
                ->comment('The date and time the payment.');

            $table->string('payer_country', 50)->nullable()
                ->comment('Full country name of the payer');

            $table->boolean('is_refunded')->default(false)
                ->comment('Indicates if the transaction has been refunded.');

            $table->boolean('has_dispute')->default(false)
                ->comment('Indicates if the payment is under dispute or chargeback.');

            $table->text('payment_note')->nullable()
                ->comment('Any optional note or remark related to the payment');

            $table->boolean('is_active')->default(true)
                ->comment('Indicates whether the payment record is active or inactive (e.g., deactivated due to errors or disputes).');

            $table->softDeletes()
                ->comment('Allows for soft deletion of payment records, enabling them to be restored later if needed.');

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
