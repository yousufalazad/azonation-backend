<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This table stores billing information for users, including active member count, billing periods, and total amounts.
     */
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();

            // Unique billing identifier
            $table->string('billing_code', 15)->unique()->comment('Unique 15-character alphanumeric transaction ID with prefix AZON-BILL.');
            //$table->string('billing_code', 15)()->comment('Unique 15-character alphanumeric transaction ID with prefix AZON-BILL.');

            // Foreign key linking to the 'users' table
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('References the user for billing. Null if user is deleted.');

            // Store user details statically for historical reference
            $table->string('user_name', 255)
                ->nullable()
                ->comment('User name snapshot for billing reference');

            

                // Service and billing month details
            $table->string('service_month')->nullable()->comment('Month in which the service was consumed');
            $table->string('billing_month')->nullable()->comment('Month in which the bill is generated');

            $table->tinyInteger('service_year', 4)
                ->nullable()
                ->comment('Year for billing reference');
            
            $table->tinyInteger('billing_year', 4)
                ->nullable()
                ->comment('Year for billing reference');

            // Brief description of the bill
            $table->string('description', 255)
            ->nullable()
            ->comment('Optional detailed description of the bill.');

            $table->string('billing_address', 255)
                ->nullable()
                ->comment('User billing address snapshot for reference');

            // Billing item details
            $table->string('item_name', 255)->comment('Name or description of the billed item or service');

            // Billing period
            $table->date('period_start')->comment('Billing period start date');
            $table->date('period_end')->comment('Billing period end date');

            // Member count data from active_member_counts table
            $table->integer('total_active_member', 10)->comment('Daily total active members within billing period');
            $table->integer('total_billable_active_member', 10)->comment('Total billable active members within billing period');

            // Billing rate and calculation
            $table->decimal('price_rate', 10, 2)->comment('Daily rate per active member');
            $table->decimal('bill_amount', 10, 2)->comment('Total bill amount based on rate, members, and days');

            // Billing status and administrative note
            $table->enum('status', ['issued', 'unissued', 'pending', 'cancelled', 'draft'])
                ->default('issued')
                ->comment('Billing status: issued, unissued, pending, cancelled, or draft');

            $table->string('admin_notes', 255)->nullable()->comment('Administrative notes for reference');

            // Active or inactive status flag
            $table->boolean('is_active')->default(true)->comment('Indicates if the billing record is active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'billings' table if needed.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
