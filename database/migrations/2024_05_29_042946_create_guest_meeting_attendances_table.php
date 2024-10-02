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
        Schema::create('guest_meeting_attendances', function (Blueprint $table) {
            $table->id();

            // Foreign key to the meetings table using foreignId and constrained
            $table->foreignId('meeting_id')
                  ->constrained('meetings')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the meetings table');

            // Guest details and meeting attendance information
            $table->string('guest_name')->nullable()->comment('Name of the guest attending the meeting');
            $table->text('description')->nullable()->comment('Description or details about the guest');
            
            // Attendance type: 0 = null, 1 = in_person, 2 = remote, 3 = hybrid
            $table->enum('attendance_type', [0, 1, 2, 3])
                  ->default(0)
                  ->nullable()
                  ->comment('Attendance type: 0 = null, 1 = in_person, 2 = remote, 3 = hybrid');

            // Date and time of attendance
            $table->date('date')->nullable()->comment('Date of guest attendance');
            $table->time('time')->nullable()->comment('Time of guest attendance');

            // Notes and status
            $table->text('note')->nullable()->comment('Additional notes regarding the guest attendance');
            $table->enum('status', [0, 1])
                  ->default(0)
                  ->nullable()
                  ->comment('Attendance status: 0 = inactive, 1 = active');

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_meeting_attendances');
    }
};
