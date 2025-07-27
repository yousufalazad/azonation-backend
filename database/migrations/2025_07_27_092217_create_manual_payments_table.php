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
        Schema::create('manual_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')
                ->constrained('payments')
                ->onDelete('cascade')
                ->comment('Reference to the main payments table');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User who submitted the manual payment (nullable for anonymous)');

            $table->foreignId('added_by_admin_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Admin user who recorded this manual payment');

            $table->string('payment_method')->nullable()
                ->comment('Payment method used, e.g., bank transfer, cash, cheque, etc.');

            $table->string('reference_number')->nullable()
                ->comment('Cheque no, transaction reference, bank slip ID, etc.');

            $table->boolean('is_verified')->default(false)
                ->comment('Flag to indicate if the payment has been verified by admin');
             
            $table->string('payer_name')->nullable()
                ->comment('Name of the person who made the manual payment');
            $table->string('payer_email')->nullable()
                ->comment('Email of the person who made the manual payment, if available');
            $table->string('payer_phone')->nullable()
                ->comment('Phone number of the person who made the manual payment, if available');
            $table->string('payer_country')->nullable()
                ->comment('Country of the person who made the manual payment, if available');
            
            $table->text('admin_note')->nullable()
                ->comment('Optional note or explanation from admin or payer');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_payments');
    }
};
