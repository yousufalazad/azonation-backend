<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gateway_name'); // stripe, paypal, bkash, etc.
            $table->boolean('is_active')->default(true);
            $table->json('credentials'); // store API keys/secrets securely (encrypted if possible)
            $table->enum('environment', ['sandbox', 'live'])->default('sandbox');
            $table->timestamps();
        });
    }

    

    
    public function down(): void
    {
        //
    }
};
