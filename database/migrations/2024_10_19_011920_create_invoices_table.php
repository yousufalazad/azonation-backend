<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Unique Invoice ID
            $table->string('invoice_code', 15)->unique()->nullable()->comment('Unique 15-character alphanumeric transaction ID with prefix AZON-INV.');

            // Foreign key linking to the 'orders' table
            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->onDelete('cascade')
                ->comment('Reference to the order for the invoice');

            // Foreign key linking to the 'users' table 
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Reference to the user who owns the invoice');

            $table->string('user_name', length: 100)->nullable()->comment('User name snapshot for billing reference');

            $table->string('description', length: 100)->nullable()->comment('Description for invoice reference');

            // Financial fields
            $table->decimal('total_amount', 10, 2)->comment('Final amount due after discounts, credit and taxes and etc.');
            $table->decimal('amount_paid', 10, 2)->default(0)->nullable()->comment('The total amount paid towards the invoice so far.');
            $table->decimal('balance_due', 10, 2)->comment('The remaining amount still owed.');

            $table->string('currency_code', 3)->comment('Currency code for the order amount');

            // Date columns
            $table->date('generate_date')->nullable()->comment('Date when the invoice was generated');
            $table->date('issue_date')->nullable()->comment('Date when the invoice was issued');
            $table->date('due_date')->nullable()->comment('Payment due date');

            $table->string('terms', 100)->nullable()->comment('Payment terms for the invoice');

            // Additional fields
            $table->string('invoice_note', 255)->nullable()->comment('Notes for the invoice, like Non-refundable');

            // Publishing and status fields
            $table->boolean('is_published')->default(false)->comment('Indicates if the invoice is published');

            // Invoice status
            $table->enum('invoice_status', ['issued', 'unissued', 'pending', 'cancelled', 'draft'])
                ->default('issued')
                ->comment('Invoice status: issued, unissued, pending, cancelled, or draft');

            $table->enum('payment_status', ['paid', 'unpaid', 'cancelled', 'refunded', 'collections', 'payment_pending', 'processing'])
                ->default('unpaid')
                ->comment('Current payment status of the invoice');

            $table->string('admin_note')->nullable()->comment('Internal admin note for reference');

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
