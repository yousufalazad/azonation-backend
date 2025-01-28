<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            // Name of the currency (e.g., US Dollar, British Pound)
            $table->string('currency_name', 50)->comment('Full name of the currency (e.g., US Dollar, British Pound)');

            // ISO 4217 currency code (e.g., USD, GBP)
            $table->string('currency_code', 3)->unique()->comment('ISO 4217 currency code (e.g., USD for US Dollar, GBP for British Pound)');

            // Currency symbol (e.g., $, £)
            $table->string('currency_symbol', 3)->comment('Symbol of the currency (e.g., $ for USD, £ for GBP)');

            // Unit name for fractional currency (e.g., cent for USD, pence for GBP)
            $table->string('unit_name')->nullable()->comment('Name of the fractional currency unit (e.g., cent for USD, pence for GBP)');

            // Currency status (active/inactive)
            $table->boolean('is_active')->default(true)->comment('Status of the currency (true = active, false = inactive)');

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
