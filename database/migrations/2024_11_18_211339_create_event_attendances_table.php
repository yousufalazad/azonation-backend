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
        Schema::create('event_attendances', function (Blueprint $table) {
            $table->id();

            // Foreign keys using foreignId and constrained
            $table->foreignId('org_event_id')
                  ->constrained('org_events')
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the org_events table');

            //Attendee
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the users table');

            // Foreign keys using foreignId and constrained
            $table->foreignId('attendance_type_id')
            ->constrained('attendance_types')
            ->nullable()
            ->comment('Foreign key referencing the attendance_types table');

            // Date and time for attendance
            $table->time('time')->nullable()->comment('Time of the meeting');

            // Text field for additional notes
            $table->text('note')->nullable()->comment('Additional notes about the attendance');

            // Enum for is_active: 0 = no, 1 = yes
            $table->boolean('is_active')
                  ->default(1)
                  ->comment('0 = false, 1 = true')
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
        Schema::dropIfExists('event_attendances');
    }
};
