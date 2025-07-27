<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('gateway_credentials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_gateway_id')
                ->constrained('payment_gateways')
                ->onDelete('cascade')
                ->comment('Reference to the supported payment gateway');

            $table->enum('mode', ['live', 'sandbox'])->default('sandbox')
                ->comment('Environment mode for this gateway credential');

            $table->string('key')->comment('Encrypted API key');
            $table->string('secret')->comment('Encrypted API secret');

            $table->string('webhook_secret')->nullable()
                ->comment('Webhook secret for validating incoming requests');

            $table->string('merchant_id')->nullable()
                ->comment('Merchant ID or account identifier from the gateway');

            $table->json('extra_config')->nullable()
                ->comment('Optional metadata: region, scopes, return URLs, etc.');

            $table->boolean('is_active')->default(true)
                ->comment('Whether this credential set is currently usable');

            $table->timestamps();
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('gateway_credentials');
    }
};
