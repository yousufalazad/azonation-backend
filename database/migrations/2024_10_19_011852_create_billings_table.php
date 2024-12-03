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
        Schema::create('billings', function (Blueprint $table) {
            $table->id()->comment('Primary key: auto-incremented ID');

            // Unique billing identifier
            $table->string('billing_code', 15)->unique()->comment('Unique 15-character alphanumeric transaction ID with prefix AZON-BILL.');

            // Foreign key linking to the 'users' table
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('References the user for billing. Null if user is deleted.');

            // Static user details for reference
            $table->string('user_name', 255)->nullable()->comment('User name snapshot for billing reference');

            // Service and billing month details
            $table->string('service_month', 20)->nullable()->comment('Month in which the service was consumed');
            $table->string('billing_month', 20)->nullable()->comment('Month in which the bill is generated');

            $table->smallInteger('service_year')->nullable()->comment('Year for billing reference');
            $table->smallInteger('billing_year')->nullable()->comment('Year for billing reference');

            // Brief description and billing address
            $table->string('description', 255)->nullable()->comment('Optional detailed description of the bill.');
            $table->string('billing_address', 255)->nullable()->comment('User billing address snapshot for reference');

            // Billing item details
            $table->string('item_name', 255)->comment('Name or description of the billed item or service');

            // Billing period
            $table->date('period_start')->comment('Billing period start date');
            $table->date('period_end')->comment('Billing period end date');

            // Member count data from active_member_counts table
            $table->integer('total_active_member')->comment('Daily total active members within billing period');
            $table->integer('total_billable_active_member')->comment('Total billable active members within billing period');

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
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};