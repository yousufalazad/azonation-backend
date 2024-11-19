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
        Schema::create('event_dignitaries', function (Blueprint $table) {
            $table->id();

            // Foreign key referencing event_summaries table
            $table->foreignId('event_summary_id')
                ->constrained('event_summaries')
                ->cascadeOnDelete()
                ->comment('Foreign key referencing event_summaries table');

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
        Schema::dropIfExists('event_dignitaries');
    }
};
