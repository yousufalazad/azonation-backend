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
        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->id();

            // Foreign key to meetings table using foreignId and constrained
            $table->foreignId('meeting_id')
                  ->constrained('meetings')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the meetings table');

            // Minutes, decisions, and additional notes
            $table->text('minutes')->nullable()->comment('Summary of minutes from the meeting');
            $table->text('decisions')->nullable()->comment('Decisions made during the meeting');
            $table->text('note')->nullable()->comment('Additional notes for the meeting');

            // Status: 0 = inactive, 1 = active
            $table->tinyInteger('status')->default(0)->comment('0 = inactive, 1 = active')->nullable();

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_minutes');
    }
};
