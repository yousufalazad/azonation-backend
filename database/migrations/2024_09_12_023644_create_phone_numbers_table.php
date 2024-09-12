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
        Schema::create('phone_numbers', function (Blueprint $table) {
            $table->id();
            
            // Foreign key linking to the users table
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade deletion of phone numbers when user is deleted

            // Foreign key linking to the country_codes table for dialing codes
            $table->foreignId('dialing_code_id')
                ->constrained('dialing_codes') 
                ->onDelete('cascade');
            // Phone number and details
            $table->string('phone_number'); // Use string for phone number to handle different formats

            // Type of phone number with comments for clarification
            $table->tinyInteger('phone_type')->nullable()->default(0)
                ->comment('0 = Other, 1 = Mobile, 2 = Home, 3 = Work'); // Type of phone number
            
            $table->boolean('status')->nullable()->default(false); // Verification status, defaulting to not verified

            // Timestamps for creation and updates
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_numbers');
    }
};
