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
            $table->foreignId('responsible_person_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User responsible for the asset assignment.');

            // Assignment start date
            $table->date('assign_start_date')
                ->nullable()
                ->comment('Date when the asset was assigned.');

            // Assignment end date
            $table->date('assign_end_date')
                ->nullable()
                ->comment('Date when the asset assignment ended.');

            // Status of the asset during this log (active, inactive, maintenance, disposed)
            $table->enum('status', ['active', 'inactive', 'under_maintenance', 'disposed'])
                ->comment('The status of the asset at the time of the log.');

            // Optional notes regarding the assignment or status change
            $table->text('note')
                ->nullable()
                ->comment('Any additional notes about the assignment or status change.');

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
