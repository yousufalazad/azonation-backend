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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')
                ->constrained('regions')
                ->cascadeOnDelete()
                ->unique() 
                ->comment('Foreign key referencing the regions table');

            $table->decimal('tax_rate', 3, 2)->comment('Tax rate for the region in percentage');
            
            $table->boolean('is_active')->default(true)->comment('Whether the regional tax rate is active or inactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
