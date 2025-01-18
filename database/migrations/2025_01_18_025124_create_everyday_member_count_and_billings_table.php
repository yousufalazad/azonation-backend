<?php

use App\Models\Currency;
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
        Schema::create('everyday_member_count_and_billings', function (Blueprint $table) {
            $table->id();

            // Foreign key referencing the users table, org primary id
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated recognitions

            $table->date('date')->unique()->comment('Date of the record');
            $table->integer('total_member')->default(0)->comment('Todays total member count');
            $table->string('currency')->comment('Currency associated with the total bill');
            $table->integer('price_rate')->default(0)->comment('Todays price rate per member from price table');
            $table->integer('todays_total_bill')->default(0)->comment('Todays total bill amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('everyday_member_count_and_billings');
    }
};
