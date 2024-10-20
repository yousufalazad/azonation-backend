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

            // Foreign key linking to the 'users' table
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->comment('Foreign key linking to the users table, cascades on delete if the user is removed.');

            // Store the org name as static data for future reference
            $table->string('user_name')
                ->nullable()
                ->comment('Stores the user name for future reference even if the user is deleted');


            // The start and end period for the billing cycle
            $table->date('start_period')->comment('The start date of the billing period.');
            $table->date('end_period')->comment('The end date of the billing period.');


            // Number of days within the billing period
            $table->integer('days')->comment('The number of days in the billing period.');

            // Rate applied per member per day (rate can change depending on region or plan)
            $table->decimal('rate', 10, 2)->comment('The rate applied per active member per day in unit price. like cent or pence');

            // Total amount billed (calculated as total_active_member * days * rate)
            $table->decimal('amount', 15, 2)->comment('The total amount calculated for the billing period.');

            // Status to track whether the billing record is issued, unissued, pending, cancelled, or a draft
            $table->enum('bill_status', ['issued', 'unissued', 'pending', 'cancelled', 'draft'])
                ->default('issued')
                ->comment('The status of the billing: issued, unissued, pending, cancelled, or draft');

            // Bill status (active/inactive)
            $table->boolean('status')->default(true)->comment('Indicates if the bill is active (true) or inactive (false)');


            // Timestamp columns
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
