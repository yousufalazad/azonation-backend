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
        Schema::create('region_gateway_defaults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')
                ->constrained('regions')
                ->cascadeOnDelete()
                ->unique()
                ->comment('Foreign key referencing the regions table');

            $table->foreignId('payment_gateway_id')
                ->nullable()
                ->constrained('payment_gateways')
                ->onDelete('set null')
                ->comment('References the payment_gateways table');

            $table->boolean('is_default')->default(false)
                ->comment('Whether this gateway is the default for the region');

            $table->string('admin_note')->nullable()
                ->comment('Admin note for the default gateway setting, e.g., "Default for BD region"');

            $table->boolean('is_active')->default(true)
                ->comment('Whether this default setting is currently active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('region_gateway_defaults');
    }
};
