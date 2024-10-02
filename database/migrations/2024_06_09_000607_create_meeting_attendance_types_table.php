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
        Schema::create('meeting_attendance_types', function (Blueprint $table) {
            $table->id();

            // Name of the attendance type (e.g., in-person, remote, hybrid)
            $table->string('name')
                  ->unique()
                  ->comment('Unique name representing the meeting attendance type');

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_attendance_types');
    }
};
