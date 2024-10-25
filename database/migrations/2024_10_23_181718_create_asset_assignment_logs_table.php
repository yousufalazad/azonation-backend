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
        Schema::create('asset_assignment_logs', function (Blueprint $table) {
            $table->id();

            // Foreign key to the asset being assigned
            $table->foreignId('asset_id')
                ->constrained('assets')
                ->onDelete('cascade')
                ->comment('The asset that is being assigned.');

            // Foreign key to the user responsible for the assignment
            $table->foreignId('responsible_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User responsible for the asset assignment.');

            // Assignment start date
            $table->date('assignment_start_date')
                ->nullable()
                ->comment('Date when the asset assignment started.');

            // Assignment end date
            $table->date('assignment_end_date')
                ->nullable()
                ->comment('Date when the asset assignment ended.');

            // Status of the asset end of the assignment 
            $table->foreignId('asset_lifecycle_statuses_id')
            ->nullable()
            ->constrained('asset_lifecycle_statuses')
            ->onDelete('set null')
            ->comment('Current status of the asset.');

             // Any additional notes regarding the asset
             $table->string('note', 255)
             ->nullable()
             ->comment('Any additional notes about the asset.');

            // Status of the asset
            $table->boolean('is_active')
                ->default(true)
                ->comment('Indicates whether the asset is currently active.');

            // Log created/updated timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_assignment_logs');
    }
};
