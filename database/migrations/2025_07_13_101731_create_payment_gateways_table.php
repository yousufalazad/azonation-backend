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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('e.g., stripe, paypal, bkash');
            $table->string('display_name')->nullable()->comment('Shown to end-users, e.g., Stripe, PayPal, bKash');
            $table->string('type')->nullable()->comment('e.g., card, wallet, bank_transfer, mobile_money');
            $table->string('region')->nullable()->comment('Target region like global, BD, EU, etc.');
            $table->boolean('is_active')->default(true)->comment('Enabled or disabled for use');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
