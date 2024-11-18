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
        Schema::create('project_attendances', function (Blueprint $table) {
            $table->id();

            // Foreign keys using foreignId and constrained
            $table->foreignId('org_project_id')
                  ->constrained('org_projects')
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the org_projects table');

            //Attendee
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the users table');

            // Foreign keys using foreignId and constrained
            $table->foreignId('meeting_attendance_type_id')
                  ->constrained('meeting_attendance_types')
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the meeting_attendance_types table');

            // Date and time for attendance
            $table->time('time')->nullable()->comment('Time of the meeting');

            // Text field for additional notes
            $table->text('note')->nullable()->comment('Additional notes about the attendance');

            // Enum for status: 0 = inactive, 1 = active
            $table->enum('status', [0, 1])
                  ->default(0)
                  ->comment('0 = inactive, 1 = active')
                  ->nullable();

            // Timestamps for when the record was created or updated
            $table->timestamps();

            // Soft deletes to allow recovering deleted records
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_attendances');
    }
};
