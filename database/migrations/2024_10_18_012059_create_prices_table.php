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
            $table->decimal('tier1', 10, 2)->comment('Price for: Rest of the World, currency: USD');
            $table->decimal('tier2', 10, 2)->comment('Price for: UK, currency: GBP');
            $table->decimal('tier3', 10, 2)->comment('Price for: USA, currency: USD');
            $table->decimal('tier4', 10, 2)->comment('Price for: Canada, currency: CAD');
            $table->decimal('tier5', 10, 2)->comment('Price for: European Union, currency: EUR');
            $table->decimal('tier6', 10, 2)->comment('Price for: China, currency: CNY');
            $table->decimal('tier7', 10, 2)->comment('Price for: Bangladesh, currency: BDT');
            $table->decimal('tier8', 10, 2)->comment('Price for: India, currency: INR');
            $table->decimal('tier9', 10, 2)->comment('Price for: Japan, currency: JPY');
            $table->decimal('tier10', 10, 2)->comment('Price for: Malaysia, currency: MYR');
            $table->decimal('tier11', 10, 2)->comment('Price for: Russia, currency: RUB');
            $table->decimal('tier12', 10, 2)->comment('Price for: Australia and New Zealand, currency: AUD');
            $table->decimal('tier13', 10, 2)->comment('Price for: Nordic countries (Sweden, Norway, Finland, Denmark), currency: EUR');
            $table->decimal('tier14', 10, 2)->comment('Price for: South America, currency: USD');
            $table->decimal('tier15', 10, 2)->comment('Price for: Middle East, currency: USD');
            $table->decimal('tier16', 10, 2)->comment('Price for: Asia (excluding Bangladesh, China, Malaysia and India), currency: USD');
            $table->decimal('tier17', 10, 2)->comment('Price for: Africa, currency: USD');
            $table->decimal('tier18', 10, 2)->comment('Price for: , currency: USD');
            $table->decimal('tier19', 10, 2)->comment('Price for: , currency: USD');
            $table->decimal('tier20', 10, 2)->comment('Price for: , currency: USD');

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
