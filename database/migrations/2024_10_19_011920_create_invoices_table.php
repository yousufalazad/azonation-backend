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

            // Unique Invoice ID (could be used for referencing invoices)
            $table->string('invoice_id', 11)->unique()->comment('Unique invoice identifier');

            // Foreign key linking to the 'users' table
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Foreign key linking to the users table, cascades on delete if the user is removed.');

                
            // Foreign key linking to the 'billings' table
            $table->foreignId('billing_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Foreign key linking to the billings table, cascades on delete');

            $table->string('item', 100)->comment('Item name');
            $table->string('description', 100)->nullable()->comment('Optional description of the invoice');

            // Dates for invoice generation, issue and payment due
            $table->date('generate_date')->comment('Date when the invoice was generated');
            $table->date('issue_date')->comment('Date when the invoice was issued');
            $table->date('due_date')->comment('Payment due date for the invoice');

            // Financial details
            $table->decimal('sub_total', 10, 2)->comment('Invoice sub total before VAT/TAX, amount comes from billing table, total billed amount');
            $table->string('discount_title', 50)->nullable()->comment('Description or title for the discount');
            $table->decimal('discount', 10, 2)->nullable()->default(0)->comment('Discount applied to the invoice');
            $table->decimal('tax', 10, 2)->nullable()->default(0)->comment('Applicable TAX or VAT on the invoice');
            $table->decimal('credit', 10, 2)->nullable()->default(0)->comment('Deduction from account credit balance');
            $table->decimal('total', 10, 2)->comment('Total amount after applying discount and TAX/VAT');
            
            // Optional note and description fields for more context
            $table->string('note', 100)->nullable()->comment('Optional note for additional details');

            // Publishing status of the invoice
            $table->boolean('published')->default(false)->comment('Flag to indicate if the invoice is published (true) or in draft (false)');

            // Status of the invoice (e.g., paid, unpaid, etc.)
            $table->enum('status', ['paid', 'unpaid', 'cancelled', 'refunded', 'collections', 'payment_pending', 'on_process'])
                ->default('unpaid')
                ->comment('Current payment status of the invoice');

            // Reason for the action status
            $table->string('action_status_reason')->nullable()->comment('Reason for the current status (e.g., overdue payment, user request, etc.)');

            //Hidden admin notes
            $table->string('hidden_admin_note')->comment('Hidden admin note for understanding');

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
