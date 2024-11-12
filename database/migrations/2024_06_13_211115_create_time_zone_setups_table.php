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
        Schema::create('time_zone_setups', function (Blueprint $table) {
            $table->id();

            // Time zone identifier (e.g., 'America/New_York', 'UTC')
            $table->string('time_zone')
                  ->comment('The name of the time zone (e.g., America/New_York, UTC)');

            // Offset from UTC (e.g., '-05:00' for New York during standard time)
            $table->string('offset')
                  ->comment('The UTC offset for the time zone (e.g., -05:00)');

            // Optional description field
            $table->text('description')
                  ->nullable()
                  ->comment('Optional description or notes about the time zone');

            $table->boolean('is_active')->default(true); // Indicates whether the time_zone_setups is active or not
            
            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_zone_setups');
    }
};
