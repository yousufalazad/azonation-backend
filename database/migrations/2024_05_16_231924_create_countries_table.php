<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration creates the `countries` table, which stores information about 
     * recognised countries using ISO-3166 standards, including alpha-3, alpha-2, 
     * and numeric codes. The table also includes an `is_active` field to indicate 
     * whether a country is active or not.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('name')
                ->unique()
                ->comment('Full country name (e.g., United States, France). Includes most recognised countries.');

            $table->string('iso_code_alpha_3', 3)
                ->unique()
                ->comment('ISO-3166 alpha-3 code (e.g., USA, FRA). Exactly 3 characters.');

            $table->string('iso_code_alpha_2', 2)
                ->comment('ISO-3166 alpha-2 code (e.g., US, FR). Exactly 2 characters.');

            $table->string('numeric_code', 3)
                ->unique()
                ->comment('ISO-3166 numeric code (e.g., 840 for USA, 250 for France). Exactly 3 digits.');

            $table->boolean('is_active')
                ->default(true)
                ->comment('Indicates whether the country is active for usage within the system.');
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * This will drop the `countries` table if it exists, effectively rolling back the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
