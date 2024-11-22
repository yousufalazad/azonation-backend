<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration creates the 'subscriptions' table to manage user subscriptions.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            // Foreign key to the 'users' table
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->unique() // Ensure that each user can only have one subscription
                ->comment('Foreign key linking to the users table. Each user can only have one active subscription, cascades on delete');

            // Foreign key to the 'packages' table
            $table->foreignId('package_id')
                ->constrained('packages')
                ->onDelete('restrict') // Prevent package deletion if referenced
                ->comment('Foreign key linking to the packages table, restrict on delete');

            $table->timestamp('start_date')->comment('The date when the subscription started');

            $table->timestamp('end_date')->nullable()->comment('The date when the subscription will end');

            // Subscription status (active/inactive)
            $table->boolean('status')->default(true)->comment('Indicates if the subscription is active (true) or inactive (false)');

            // Subscription state: hold, pending, cancelled, suspended, terminated, due_to_payment, etc.
            $table->string('action_status')->default('active')->comment('Current status of the subscription (active, hold, pending, cancelled, suspended, terminated, due_to_payment)');

            // Reason for the current subscription status
            $table->string('action_status_reason')->nullable()->comment('Reason for the current status (e.g., overdue payment, user request, etc.)');

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'subscriptions' table if needed.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
