<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
      public function up(): void
      {
            Schema::create('subscription_logs', function (Blueprint $table) {
                  $table->id();

                  // Foreign key to the 'users' table
                  $table->foreignId('user_id')
                        ->nullable()
                        ->constrained()
                        ->onDelete('set null')
                        ->comment('Foreign key linking to the users table, cascades on delete');

                  // Foreign key to the 'packages' table for the old package
                  $table->foreignId('old_package_id')
                        ->nullable()
                        ->constrained('packages')
                        ->onDelete('set null')
                        ->comment('The previous package before the change. Nullable if this is the first subscription');

                  // Store the old package name as static data for future reference
                  $table->string('old_package_name')
                        ->nullable()
                        ->comment('Stores the old package name for future reference even if the package is deleted');

                  // Start and end dates of the old package
                  $table->timestamp('old_package_start_date')
                        ->nullable()
                        ->comment('The start date of the previous package subscription');

                  $table->timestamp('old_package_end_date')
                        ->nullable()
                        ->comment('The end date of the previous package subscription (the date when the package was changed)');

                  // Foreign key to the 'packages' table for the new package
                  $table->foreignId('new_package_id')
                        ->nullable()
                        ->constrained('packages')
                        ->onDelete('set null')
                        ->comment('The new package after the change, cascades on delete');

                  // Date when the package was changed
                  $table->timestamp('change_date')->useCurrent()->comment('The date and time when the package was changed');

                  // Optional reason or note for the package change
                  $table->string('change_reason')->nullable()->comment('Optional reason or note for why the package was changed');

                  // SubscriptionLog status (active/inactive)
                  $table->boolean('status')->default(true)->comment('Indicates if the subscriptionLog info is active (true) or inactive (false)');

                  // Timestamps for created_at and updated_at
                  $table->timestamps();
            });
      }

      /**
       * Reverse the migrations.
       * Drops the 'subscription_logs' table if needed.
       */
      public function down(): void
      {
            Schema::dropIfExists('subscription_logs');
      }
};
