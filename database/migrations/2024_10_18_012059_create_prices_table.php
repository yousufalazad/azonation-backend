<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration creates the 'prices' table, which links to different packages and sets region-specific pricing.
     */
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();

            // Foreign key to the 'packages' table
            $table->foreignId('package_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->unique() // Ensure that each package_id can only appear once
                ->comment('Foreign key linking to the packages table, cascades on delete');

            // Tier pricing with notes on corresponding regions
            $table->decimal('tier1', 10, 2)->comment('Price for Tier 1: Rest of the World');
            $table->decimal('tier2', 10, 2)->comment('Price for Tier 2: UK');
            $table->decimal('tier3', 10, 2)->comment('Price for Tier 3: America');
            $table->decimal('tier4', 10, 2)->comment('Price for Tier 4: Canada');
            $table->decimal('tier5', 10, 2)->comment('Price for Tier 5: European Union');
            $table->decimal('tier6', 10, 2)->comment('Price for Tier 6: Bangladesh');
            $table->decimal('tier7', 10, 2)->comment('Price for Tier 7: China');
            $table->decimal('tier8', 10, 2)->comment('Price for Tier 8: India');
            $table->decimal('tier9', 10, 2)->comment('Price for Tier 9: Japan');
            $table->decimal('tier10', 10, 2)->comment('Price for Tier 10: Singapore');
            $table->decimal('tier11', 10, 2)->comment('Price for Tier 11: Russia');
            $table->decimal('tier12', 10, 2)->comment('Price for Tier 12: Australia and New Zealand');
            $table->decimal('tier13', 10, 2)->comment('Price for Tier 13: Nordic countries (Sweden, Norway, Finland, Denmark)');
            $table->decimal('tier14', 10, 2)->comment('Price for Tier 14: South America');
            $table->decimal('tier15', 10, 2)->comment('Price for Tier 15: Middle East');
            $table->decimal('tier16', 10, 2)->comment('Price for Tier 16: Asia (excluding Bangladesh, China and India)');
            $table->decimal('tier17', 10, 2)->comment('Price for Tier 17: Africa');

            // Status column to indicate if the pricing for the package is currently active
            $table->boolean('status')->default(true)->comment('Indicates if the pricing is active (true) or inactive (false)');

            // Timestamps for created_at and updated_at
            $table->timestamps(); //Timestamps for when the price entry was created and last updated
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'prices' table if needed.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
