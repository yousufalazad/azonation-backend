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
        Schema::create('honorary_member_counts', function (Blueprint $table) {
            $table->id();

            // Foreign key to the users table
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Foreign key linking to the users table');

            // Date column to store each specific day's count
            $table->date('date')
                ->comment('The date for the recorded active member count');

            // Column to store the total active member count for that day
            $table->integer('active_honorary_member')
                ->comment('Total active member count for billing purposes on the specified date');

            $table->boolean('is_billable')
                ->comment('Yes for billable, No for not billable');
                 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honorary_member_counts');
    }
};
