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
        Schema::create('price_rates', function (Blueprint $table) {
            $table->id();

            // Foreign key to the 'packages' table
            $table->foreignId('package_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->unique() // Ensure that each package_id can only appear once
                ->comment('Foreign key linking to the packages table, cascades on delete');

            // Per member per day pricing with notes on corresponding regions
            $table->decimal('region1', 10, 2)->comment('Price for: Rest of the World, currency: USD');
            $table->decimal('region2', 10, 2)->comment('Price for: UK, currency: GBP');
            $table->decimal('region3', 10, 2)->comment('Price for: USA, currency: USD');
            $table->decimal('region4', 10, 2)->comment('Price for: Canada, currency: CAD');
            $table->decimal('region5', 10, 2)->comment('Price for: European Union, currency: EUR');
            $table->decimal('region6', 10, 2)->comment('Price for: China, currency: CNY');
            $table->decimal('region7', 10, 2)->comment('Price for: Bangladesh, currency: BDT');
            $table->decimal('region8', 10, 2)->comment('Price for: India, currency: INR');
            $table->decimal('region9', 10, 2)->comment('Price for: Japan, currency: JPY');
            $table->decimal('region10', 10, 2)->comment('Price for: Malaysia, currency: MYR');
            $table->decimal('region11', 10, 2)->comment('Price for: Russia, currency: RUB');
            $table->decimal('region12', 10, 2)->comment('Price for: Australia and New Zealand, currency: AUD');
            $table->decimal('region13', 10, 2)->comment('Price for: Nordic countries (Sweden, Norway, Finland, Denmark), currency: EUR');
            $table->decimal('region14', 10, 2)->comment('Price for: South America, currency: USD');
            $table->decimal('region15', 10, 2)->comment('Price for: Middle East, currency: USD');
            $table->decimal('region16', 10, 2)->comment('Price for: Asia (excluding Bangladesh, China, Malaysia and India), currency: USD');
            $table->decimal('region17', 10, 2)->comment('Price for: Africa, currency: USD');
            $table->decimal('region18', 10, 2)->comment('Price for: , currency: USD');
            $table->decimal('region19', 10, 2)->comment('Price for: , currency: USD');
            $table->decimal('region20', 10, 2)->comment('Price for: , currency: USD');

            // Status column to indicate if the pricing for the package is currently active
            $table->boolean('status')->default(true)->comment('Indicates if the pricing is active (true) or inactive (false)');

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_rates');
    }
};
