<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This creates the 'invoices' table for generating invoices linked to billings and managing financial data.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Unique Invoice ID
            $table->string('invoice_code', 15)->unique()->comment('Unique 15-character alphanumeric transaction ID with prefix AZON-INV.');

            // Foreign key linking to the 'users' table
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->comment('Reference to the user who owns the invoice');

            $table->foreignId('billing_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Reference to the associated billing entry');

            // Item and description
            $table->string('item_name', 100)->comment('Name of the invoiced item or service');
            $table->string('item_description', 100)->nullable()->comment('Description of the item or service, if needed');

            // Date columns
            $table->date('generated_at')->comment('Date when the invoice was generated');
            $table->date('issued_at')->comment('Date when the invoice was issued');
            $table->date('due_at')->comment('Payment due date');

            // Financial details
            $table->decimal('subtotal_amount', 10, 2)->comment('Subtotal before discounts and taxes');
            $table->string('discount_description', 50)->nullable()->comment('Title or description of the discount');
            $table->decimal('discount_value', 10, 2)->nullable()->default(0)->comment('Discount amount applied to the invoice');
            $table->decimal('tax_amount', 10, 2)->nullable()->default(0)->comment('Total tax applied');
            $table->decimal('credit_applied', 10, 2)->nullable()->default(0)->comment('Credit amount applied from the user’s balance');
            $table->decimal('total_amount_due', 10, 2)->comment('Final amount due after discounts and taxes');

            // Additional fields
            $table->string('additional_note', 100)->nullable()->comment('Optional additional notes for the invoice');

            // Publishing and status fields
            $table->boolean('is_published')->default(false)->comment('Indicates if the invoice is published');
            $table->enum('payment_status', ['paid', 'unpaid', 'cancelled', 'refunded', 'collections', 'payment_pending', 'processing'])
                ->default('unpaid')
                ->comment('Current payment status of the invoice');

            // Action reason and hidden note
            $table->string('status_reason')->nullable()->comment('Reason for the current payment status');
            $table->string('admin_note')->comment('Internal admin note for reference');

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'invoices' table if needed.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
