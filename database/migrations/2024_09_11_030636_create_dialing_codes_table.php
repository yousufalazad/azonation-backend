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
        Schema::create('dialing_codes', function (Blueprint $table) {
            $table->id();
            
            // Dialing code for the country
            $table->string('code', 10)->index()->comment('International dialing code, e.g., +1, +44');
            
            // Country name associated with the dialing code
            $table->string('country_name', 100)->index()->comment('Name of the country, e.g., United Kingdom, United States');
            
            // Timestamps for creation and updates
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dialing_codes');
    }
};
