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

            // Address fields
            $table->string('shipping_address')->comment('Shipping address for the order');
            $table->string('billing_address')->comment('Billing address for the order');

            $table->string('payment_method', length: 20)->comment('Payment method for the invoice, e.g., PayPal, Cash on Delivery');

            // Date columns
            $table->date('generated_at')->nullable()->comment('Date when the invoice was generated');
            $table->date('issued_at')->nullable()->comment('Date when the invoice was issued');
            $table->date('due_at')->nullable()->comment('Payment due date');


            // Financial details
            $table->string('currency', length: 3)->nullable()->comment('Currency on service month');
            $table->decimal('subtotal', 10, 2)->nullable()->comment('Subtotal before discounts and taxes, data comes from billings table - bill_amount column');
            $table->string('discount_title', 50)->nullable()->comment('Title or description of the discount');
            $table->decimal('discount', 10, 2)->default(0)->comment('Discount amount applied to the invoice');
            //$table->decimal(column: 'tax_amount', 10, 2)->default(0)->comment('Total tax applied');
            $table->decimal('credit_applied', 10, 2)->default(0)->comment('Credit amount applied from the user’s balance');
            $table->decimal('balance', 10, 2)->default(0)->comment('Final amount due after discounts, credit and taxes');

            // Additional fields
            $table->string('invoice_note', 100)->nullable()->comment('Notes for the invoice, like Non-refundable');

            // Publishing and status fields
            $table->boolean('is_published')->default(false)->comment('Indicates if the invoice is published');

            // Invoice status
            $table->enum('invoice_status', ['issued', 'unissued', 'pending', 'cancelled', 'draft'])
                ->default('issued')
                ->comment('Invoice status: issued, unissued, pending, cancelled, or draft');

            $table->enum('payment_status', ['paid', 'unpaid', 'cancelled', 'refunded', 'collections', 'payment_pending', 'processing'])
                ->default('unpaid')
                ->comment('Current payment status of the invoice');

            // Action reason and hidden note
            $table->string('payment_status_reason')->nullable()->comment('Reason for the current payment status');
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
