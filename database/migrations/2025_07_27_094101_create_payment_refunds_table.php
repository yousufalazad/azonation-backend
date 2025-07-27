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
        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')
                ->constrained('payments')
                ->onDelete('cascade')
                ->comment('Linked payment record being refunded');

            $table->enum('refund_type', ['manual', 'gateway'])->default('gateway')
                ->comment('Type of refund: initiated manually or via gateway API');

            $table->decimal('refund_amount', 10, 2)->default(0)
                ->comment('Amount refunded (can be partial)');

            $table->string('currency', 3)->default('USD')
                ->comment('Currency used in refund');

            $table->timestamp('refund_time')->nullable()
                ->comment('Date and time refund was processed');

            $table->string('gateway_refund_id')->nullable()
                ->comment('Refund reference ID returned by the gateway (if applicable)');

            $table->foreignId('refunded_by_user_id')->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Admin user who processed the refund (manual refunds)');

            $table->string('refund_reason')->nullable()
                ->comment('Reason for refund');

            $table->enum('status', ['initiated', 'processing', 'successful', 'failed'])->default('initiated')
                ->comment('Current status of refund');

            $table->json('refund_response')->nullable()
                ->comment('Full refund response from gateway API (if applicable)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
    }
};
