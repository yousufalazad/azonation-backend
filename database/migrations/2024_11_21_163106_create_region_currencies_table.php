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
        Schema::create('region_currencies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')
                ->constrained('regions')
                ->cascadeOnDelete()
                ->unique()
                ->comment('Foreign key referencing the regions table');

            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->cascadeOnDelete()
                ->comment('Foreign key referencing the currencies table');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('region_currencies');
    }
};
