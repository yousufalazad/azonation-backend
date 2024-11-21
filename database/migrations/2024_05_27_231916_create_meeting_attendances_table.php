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
        Schema::create('meeting_attendances', function (Blueprint $table) {
            $table->id();

            // Foreign keys using foreignId and constrained
            $table->foreignId('meeting_id')
                  ->constrained('meetings')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the meetings table');

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the users table');

            // Foreign keys using foreignId and constrained
            $table->foreignId('attendance_type_id')
            ->constrained('attendance_types')
            ->nullable()
            ->comment('Foreign key referencing the attendance_types table');

            // Date and time for attendance
            $table->date('date')->nullable()->comment('Date of the meeting');
            $table->time('time')->nullable()->comment('Time of the meeting');

            // Text field for additional notes
            $table->text('note')->nullable()->comment('Additional notes about the attendance');

            // Enum for status: 0 = inactive, 1 = active
            $table->enum('is_active', [0, 1])
                  ->default(1)
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
        Schema::dropIfExists('meeting_attendances');
    }
};
