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
        Schema::create('histories', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key to the users table (organization owner)
            $table->foreignId('user_id')
                ->unique() // Ensures one history per organization
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated history

            // Organization history details
            $table->string('title', 255)->nullable(); // Limit title to 255 characters
            $table->string('image')->nullable(); // Optional image for the history
            $table->longText('history')->nullable(); // Detailed history of the organization, limit handled in app layer
            $table->string('document')->nullable(); // File path for the document, 1 MB limit handled in app layer

            // Status of the history record
            $table->boolean('status')->default(1)->comment('0 = inactive, 1 = active');

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
