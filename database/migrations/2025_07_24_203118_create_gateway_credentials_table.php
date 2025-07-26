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
        Schema::create('gateway_credentials', function (Blueprint $table) {
            $table->string('gateway'); // e.g., stripe, paypal, sslcommerz
            $table->string('key'); // Encrypted API key
            $table->string('secret'); // Encrypted API secret

            $table->json('extra_config')->nullable(); // Optional JSON metadata (e.g., webhook secret, environment)

            $table->string('status')->default('active'); // active, inactive
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateway_credentials');
    }
};
