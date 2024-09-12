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
        // Schema::create('dialing_codes', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
        //     $table->string('dialing_code');
        //     $table->timestamps();
        // });

        Schema::create('dialing_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')
                ->constrained('countries')
                ->onDelete('cascade')
                ->onUpdate('cascade'); // Ensures updates on 'countries' cascade
            $table->string('dialing_code', 10)->index(); // Limited length and indexed for faster lookups
            $table->timestamps();

            // Add a unique constraint to avoid duplicate dialing codes for the same country
            $table->unique(['country_id', 'dialing_code']);
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
