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
        Schema::create('project_guest_attendances', function (Blueprint $table) {
            $table->id();

            // Foreign key to the meetings table using foreignId and constrained
            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the org_projects table');

            // Guest details and meeting attendance information
            $table->string('guest_name')->nullable()->comment('Name of the guest attending the meeting');
            $table->text('about_guest')->nullable()->comment('Description or details about the guest');
            
            // Foreign keys using foreignId and constrained
            $table->foreignId('attendance_type_id')
                    ->constrained('attendance_types')
                    ->nullable()
                    ->comment('Foreign key referencing the attendance_types table');

            // Date and time of attendance
            $table->date('date')->nullable()->comment('Date of guest attendance');
            $table->time('time')->nullable()->comment('Time of guest attendance');

            // Notes and status
            $table->text('note')->nullable()->comment('Additional notes regarding the guest attendance');
            // Enum for status: 0 = inactive, 1 = active
            $table->enum('is_active', [0, 1])
                  ->default(1)
                  ->comment('0 = inactive, 1 = active')
                  ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_guest_attendances');
    }
};
