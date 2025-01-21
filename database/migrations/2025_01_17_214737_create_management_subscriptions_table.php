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
        Schema::create('management_subscriptions', function (Blueprint $table) {
            $table->id();

            // Foreign key to the 'users' table
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->unique() // Ensure that each user can only have one subscription
                ->comment('Foreign key linking to the users table. Each user can only have one active subscription, cascades on delete');

            // Foreign key to the 'management_packages' table
            $table->foreignId('management_package_id')
                ->constrained('management_packages')
                ->onDelete('restrict') // Prevent package deletion if referenced
                ->comment('Foreign key linking to the packages table, restrict on delete');

            $table->timestamp('start_date')->comment('The date when the subscription started');
            

            $table->enum('subscription_status', ['active', 'hold', 'pending', 'cancelled', 'suspended', 'terminated'])
                ->default('active')
                ->comment('Subscription status: active, hold, pending, cancelled, suspended, terminated');

            // Reason for the current subscription status
            $table->string('reason_for_action')->nullable()->comment('Reason for the current subscription status (e.g., overdue payment, user request, etc.)');
            
            // Subscription status (active/inactive)
            $table->boolean('is_active')->default(true)->comment('Indicates if the subscription is active (true) or inactive (false)');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('management_subscriptions');
    }
};
