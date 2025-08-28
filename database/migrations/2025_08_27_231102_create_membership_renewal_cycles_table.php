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
        Schema::create('membership_renewal_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Name of the renewal cycle, e.g., Annual, Biennial');
            $table->decimal('duration_in_months', 5, 2)
                ->comment('Duration of the renewal cycle in months (e.g., 1 = 1 month, 0.25 = 1 week, 0.5 = 2 weeks)');
            $table->boolean('is_active')->default(true)->comment('Indicates whether the renewal cycle is active or not');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_renewal_cycles');
    }
};
