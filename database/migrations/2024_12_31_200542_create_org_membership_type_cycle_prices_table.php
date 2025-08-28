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
        Schema::create('org_membership_type_cycle_prices', function (Blueprint $table) {
            $table->id();

            // Scope to organisation
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Organisation user id');

            // Org’s configured membership type
            $table->foreignId('org_membership_type_id')
                ->constrained('org_membership_types')
                ->cascadeOnDelete()
                ->comment('Organisation membership type');

            // Org’s allowed cycle (e.g., Monthly, Yearly) for that type
            $table->foreignId('org_membership_type_cycle_id')
                ->constrained('org_membership_type_cycles')
                ->cascadeOnDelete()
                ->comment('Organisation membership type cycle');

            // (Optional) global cycle reference to simplify joins/reporting
            $table->foreignId('membership_renewal_cycle_id')->nullable()
                ->constrained('membership_renewal_cycles')
                ->nullOnDelete();

            // Money (minor units) + currency
            $table->string('currency', 3)->comment('ISO 4217 (e.g., GBP)');
            $table->unsignedBigInteger('unit_amount_minor')->comment('Minor units, e.g., 999 = £9.99');

            $table->boolean('is_recurring')->default(true)->comment('Recurring price?');

            // Effective window (inclusive). Null = open-ended.
            $table->date('valid_from')->nullable()->comment('Effective from (inclusive)');
            $table->date('valid_to')->nullable()->comment('Effective to (inclusive)');

            $table->string('org_notes', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // ---- Constraints & indexes ----

            // Upsert-friendly uniqueness. (Avoids duplicate “starts” for the same org/type/cycle/currency)
            $table->unique(
                ['org_type_user_id', 'org_membership_type_id', 'org_membership_type_cycle_id', 'currency', 'valid_from'],
                'uq_price_from'
            );

            // Common lookup: resolve current price quickly
            $table->index(
                ['org_type_user_id', 'org_membership_type_id', 'org_membership_type_cycle_id', 'currency', 'is_active', 'valid_from'],
                'idx_price_lookup'
            );

            // Listing within an org/type with UI ordering
            $table->index(
                ['org_type_user_id', 'org_membership_type_id', 'is_active', 'sort_order'],
                'idx_price_listing'
            );

            // Helpful filters
            $table->index(['valid_from', 'valid_to'], 'idx_price_dates');
            $table->index(['currency'], 'idx_price_currency');

            // Guards (MySQL 8+)
            $table->check('valid_to IS NULL OR valid_from <= valid_to');
            $table->check('unit_amount_minor >= 0');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('org_membership_type_cycle_prices');
    }
};
