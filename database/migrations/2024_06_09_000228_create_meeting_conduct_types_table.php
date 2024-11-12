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
        Schema::create('meeting_conduct_types', function (Blueprint $table) {
            $table->id();

            // Name of the conduct type (e.g., in-person, virtual)
            $table->string('name')
                  ->unique()
                  ->comment('Unique name representing the meeting conduct type');
            
            $table->boolean('is_active')->default(true); // Indicates whether the meeting_conduct_types is active or not

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_conduct_types');
    }
};
