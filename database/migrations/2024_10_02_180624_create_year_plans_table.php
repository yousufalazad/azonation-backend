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
        Schema::create('year_plans', function (Blueprint $table) {
            $table->id();

            // Foreign key referencing the users table (creator or recipient of the plan)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Creator or responsible user for the year plan.');


            // Start year of the plan (e.g., 2024)
            $table->year('start_year')
                ->comment('The start year of the year plan.');

            // End year of the plan (e.g., 2025)
            $table->year('end_year')
                ->comment('The end year of the year plan.');

            // Description of the yearly goals
            $table->text('goals')
                ->nullable()
                ->comment('The goals and objectives for the year.');

            // List of planned activities
            $table->text('activities')
                ->nullable()
                ->comment('Planned activities for the year.');

            // Estimated budget for the year
            $table->decimal('budget', 15, 2)
                ->nullable()
                ->comment('Estimated budget allocated for the year.');

            // Status of the year plan (draft, approved, etc.)
            $table->enum('status', ['draft', 'approved', 'completed', 'archived'])
                ->default('draft')
                ->comment('The status of the year plan.');

            // Start and end dates of the year plan
            $table->timestamp('start_date')
                ->nullable()
                ->comment('The start date of the year plan.');
            $table->timestamp('end_date')
                ->nullable()
                ->comment('The end date of the year plan.');

            // Publication status (published or unpublished)
            $table->boolean('published')
                ->default(1)
                ->comment('Publication status: 1 = published, 0 = unpublished.');

                // Foreign key referencing the privacy setups table (privacy settings)
            $table->foreignId('privacy_setup_id')
            ->constrained('privacy_setups')
            ->onDelete('cascade')
            ->comment('Privacy level of the year plan (e.g., public, private, only members).');

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('year_plans');
    }
};
