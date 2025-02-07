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
        Schema::create('management_subscription_records', function (Blueprint $table) {
            $table->id();

            // Foreign key to the 'users' table
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade')
                ->comment('Foreign key linking to the users table, cascades on delete');

            // Foreign key to the 'packages' table for the old package
            $table->foreignId('old_mgmt_pakg_id')
                ->nullable()
                ->constrained('management_packages')
                ->onDelete('cascade')
                ->comment('The previous package before the change. Nullable if this is the first subscription');

            // Store the old package name as static data for future reference
            $table->string('old_mgmt_pakg_name')
                ->nullable()
                ->comment('Stores the old management package name for future reference even if the package is deleted');

            $table->decimal('old_mgmt_price_rate', 10, 2)->nullable()->comment('Previous daily rate per active member');

            // Start and end dates of the old package
            $table->timestamp('old_mgmt_pakg_start_date')
                ->nullable()
                ->comment('The start date of the previous package subscription');

            $table->timestamp('old_mgmt_pakg_end_date')
                ->nullable()
                ->comment('The end date of the previous package subscription (the date when the package was changed)');

            // Foreign key to the 'packages' table for the new package
            $table->foreignId('new_mgmt_pakg_id')
                ->nullable()
                ->constrained('management_packages')
                ->onDelete('cascade')
                ->comment('The new package after the change, cascades on delete'); 

            // Store the new package name as static data for future reference
            $table->string('new_mgmt_pakg_name')
                ->nullable()
                ->comment('Stores the new management package name for future reference even if the package is deleted');

            $table->decimal('new_mgmt_price_rate', 10, 2)->nullable()->comment('New daily rate per active member');

            $table->string('currency_code')->comment('Currency associated with the packages');

            // Date when the package was changed
            $table->timestamp('change_date')->useCurrent()->comment('The date and time when the package was changed');

            // Optional reason or note for the package change
            $table->string('change_reason')->nullable()->comment('Optional reason or note for why the package was changed');

            // Subscription record status (active/inactive)
            $table->boolean('is_active')->default(true)->comment('Indicates subscription record is active or inactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('management_subscription_records');
    }
};
