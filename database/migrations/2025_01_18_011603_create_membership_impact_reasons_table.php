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
        Schema::create('membership_impact_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Impact reason for the membership');
            $table->string('description')->nullable()->comment('Description of the impact reason');
            $table->string('note')->nullable()->comment('Additional notes for the impact reason');
            $table->boolean('is_active')->default(true)->comment('Indicates whether the impact reason is active or inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_impact_reasons');
    }
};
