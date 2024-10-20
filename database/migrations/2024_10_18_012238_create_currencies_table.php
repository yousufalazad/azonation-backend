<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration creates the 'currencies' table, which defines different currencies and links them to pricing tiers.
     */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            // Foreign key to the 'prices' table
            $table->foreignId('price_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->unique() // Ensure that each price_id can only appear once
                ->comment('Foreign key linking to the prices table, cascades on delete');

            // Name of the currency (e.g., US Dollar, British Pound)
            $table->string('name')->comment('Full name of the currency (e.g., US Dollar, British Pound)');

            // ISO 4217 currency code (e.g., USD, GBP)
            $table->string('currency_code', 3)->comment('ISO 4217 currency code (e.g., USD for US Dollar, GBP for British Pound)');

            // Currency symbol (e.g., $, £)
            $table->string('symbol', 3)->comment('Symbol of the currency (e.g., $ for USD, £ for GBP)');

            // Unit name for fractional currency (e.g., cent for USD, pence for GBP)
            $table->string('unit_name')->nullable()->comment('Name of the fractional currency unit (e.g., cent for USD, pence for GBP)');

            // Exchange rate to a reference currency (optional, but globally useful for calculations)
            $table->decimal('exchange_rate', 15, 6)->nullable()->comment('Exchange rate of this currency relative to the USD');

            // Currency status (active/inactive)
            $table->boolean('status')->default(true)->comment('Status of the currency (true = active, false = inactive)');

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'currencies' table if needed.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
