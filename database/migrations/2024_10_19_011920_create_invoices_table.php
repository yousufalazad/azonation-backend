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
            $table->string('invoice_number')->unique()->comment('Unique invoice identifier');

            // Foreign key linking to the 'billings' table
            $table->foreignId('billing_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Foreign key linking to the billings table, cascades on delete');

            // Dates for invoice generation, issue and payment due
            $table->date('generate_date')->comment('Date when the invoice was generated');
            $table->date('issue_date')->comment('Date when the invoice was issued');
            $table->date('due_date')->comment('Payment due date for the invoice');

            // Financial details
            $table->decimal('sub_total', 10, 2)->comment('Invoice sub total before VAT/TAX');
            $table->decimal('discount', 10, 2)->nullable()->default(0)->comment('Discount applied to the invoice');
            $table->string('discount_title')->nullable()->comment('Description or title for the discount');
            $table->decimal('tax', 10, 2)->nullable()->default(0)->comment('Applicable TAX or VAT on the invoice');
            $table->decimal('total', 10, 2)->comment('Total amount after applying discount and TAX/VAT');

            // Invoice item details
            $table->string('item_name')->comment('Description or name of the service/item billed');
            $table->integer('total_active_members')->default(0)->comment('Total number of active members associated with the billing period');
            $table->integer('total_days')->default(0)->comment('Total number of days for which the service is being billed');
            $table->decimal('rate', 10, 2)->comment('Rate per day per active member');
            $table->decimal('amount', 10, 2)->comment('Calculated total amount based on rate, members, and days');

            // Optional note and description fields for more context
            $table->text('note')->nullable()->comment('Optional note for additional details');
            $table->text('description')->nullable()->comment('Optional description of the invoice');

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
