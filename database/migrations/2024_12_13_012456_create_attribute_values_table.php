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
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();  // Unique ID for the attribute value
            
            $table->foreignId('attribute_id')  // Foreign key referencing the attributes table
                ->constrained('attributes')
                ->onDelete('cascade')
                ->comment('Foreign key: Links to the attributes table');

            $table->string('value')  // The actual value of the attribute (e.g., "M", "Red", "Cotton")
                ->comment('The value of the attribute (e.g., M, Red, Cotton)');

            $table->boolean('is_active')->default(true)->comment('Whether the attribute_values is active or inactive');


            $table->timestamps();  // Laravel will automatically create created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
