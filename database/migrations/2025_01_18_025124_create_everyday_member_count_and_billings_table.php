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
            $table->integer('day_total_member')->comment('Day total member count');
            //$table->decimal('price_rate', 10, 2)->nullable()->comment('Daily rate per member');
            $table->integer('day_total_bill')->comment('day_total_member * price_rate');
            $table->boolean('is_active')->default(true)->comment('Indicates if the day bill is active');
           
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
