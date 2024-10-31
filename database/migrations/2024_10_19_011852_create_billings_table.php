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

            $table->string('item_name')->comment('Description or name of the service/item billed');
            
            //from billable_day_counts table (sum of every day total within bill period)
            $table->integer('active_members_sum')->default(0)->comment('Sum of everyday active members count within the billing period, data comes from billable_day_counts table');
            
            //total number of days within the bill period
            $table->integer('total_days')->default(0)->comment('Total number of days for which the service is being billed');
            $table->decimal('rate', 10, 2)->comment('Rate per day per active member');
            $table->decimal('bill_amount', 10, 2)->comment('Calculated total amount based on rate, members, and days');

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
