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
        Schema::create('storage_pricings', function (Blueprint $table) {
            $table->id();
            // Foreign key referencing the regions table
            $table->foreignId('region_id')
                ->constrained('regions')
                ->cascadeOnDelete()
                ->comment('References the regions table; deletes related prices when the region is deleted.');

            // Foreign key referencing the packages table
            $table->foreignId('storage_package_id')
                ->constrained('storage_packages')
                ->cascadeOnDelete()
                ->comment('References the packages table; deletes related prices when the package is deleted.');

            // Price field with a precision of 10 and 2 decimal places
            $table->decimal('price_rate', 10, 2)
                ->comment('The unit price for the package in this region, stored as minor currency units (e.g., cents, pence, poysha). Divide by 100 to get the major currency value.');

            // Note field to provide additional information
            $table->string('note', 100)
                ->nullable()
                ->comment('Optional note or description for this pricing record.');

            $table->boolean('is_active')->default(true)->comment('Indicates whether the storage package is active or not');

            // Unique constraint to prevent duplicate region-package combinations
            $table->unique(['region_id', 'storage_package_id'], 'unique_region_storage_package');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_pricings');
    }
};
