<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'success_stories' table.
     */
    public function up(): void
    {
        Schema::create('success_stories', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key referencing the users table (story author or related user)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated success stories

            // Success story details
            $table->string('title')->nullable(); // Optional title for the success story
            $table->string('image')->nullable(); // Optional image for the success story
            $table->longText('story')->nullable(); // Detailed success story content

            // Status of the success story
            $table->boolean('status')->default(1)->comment('0 = inactive, 1 = active');

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'success_stories' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('success_stories');
    }
};
