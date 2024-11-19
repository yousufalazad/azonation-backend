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
        Schema::create('meeting_dignitaries', function (Blueprint $table) {
            $table->id();

            // Foreign key referencing meeting_minutes table
            $table->foreignId('meeting_minute_id')
                ->constrained('meeting_minutes')
                ->cascadeOnDelete()
                ->comment('Foreign key referencing meeting_minutes table');

            // Name of the participant, role, organisation, and details about the participant
            $table->string('role')->comment('Role of the dignitaries (e.g., Speaker, Chief Guest, Special Guest)');
            $table->string('name')->comment('Name of the dignitaries');
            $table->string('designation')->comment('Role of the dignitaries (e.g., Speaker, Chief Guest, Special Guest)');
            $table->string('organisation')->nullable()->comment('Organisation of the dignitaries, if applicable');
            $table->text('details')->nullable()->comment('Additional details about the dignitaries');
            $table->string('note')->nullable()->comment('Additional notes about the dignitaries');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_dignitaries');
    }
};
