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
            $table->string('region'); // e.g. IN, US, CN, GLOBAL
            $table->string('gateway'); // e.g. stripe, paypal
            $table->string('environment')->default('sandbox');
            $table->string('status')->default('active');
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
