<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('everyday_member_count_and_billings', function (Blueprint $table) {
            $table->id();

            // Foreign key referencing the users table, org primary id
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated recognitions

            $table->date('date')->unique()->comment('Date of the record');
            $table->integer('total_member')->default(0)->comment('Day total member count');
            $table->string('currency')->comment('Currency associated with the total bill');
            $table->integer('day_total_bill')->default(0)->comment('total_member * price_rate');
            $table->decimal('price_rate', 10, 2)->nullable()->comment('Daily rate per active member');
           
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
