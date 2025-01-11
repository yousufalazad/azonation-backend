<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'founders' table.
     */
    public function up(): void
    {
        Schema::create('founders', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key referencing the users table (for organization)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade') // Cascade on delete to remove associated founders
                ->comment('Organisation ID'); 

            $table->foreignId('founder_user_id')
                ->constrained('users')
                ->onDelete('cascade') // Cascade on delete to remove associated founders 
                ->nullable() // Allow null values
                ->comment('Individual ID'); 

            // Founder details
            $table->string('name'); // name of the founder
            $table->string('designation')->nullable(); // designation of the founder

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'founders' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('founders');
    }
};
