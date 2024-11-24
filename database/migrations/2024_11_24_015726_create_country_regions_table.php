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
        Schema::create('country_regions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                ->constrained('countries')
                ->cascadeOnDelete()
                ->unique()
                ->comment('Foreign key referencing the countries table');

            $table->foreignId('region_id')
                ->constrained('regions')
                ->cascadeOnDelete()
                ->comment('Foreign key referencing the regions table');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_regions');
    }
};
