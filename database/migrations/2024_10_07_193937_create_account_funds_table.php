<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('account_funds', function (Blueprint $table) {
            $table->id();
            
            // Foreign key referencing the users table (creator or responsible person for the fund)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('References the user responsible for managing this fund.');

            // Name of the fund, with a maximum length of 255 characters
            $table->string('name', 255)
                ->comment('The name of the fund.');

            // Fund status: 1 = Active, 0 = Inactive
            $table->boolean('status')
                ->default(1)
                ->comment('Fund status: 1 = Active, 0 = Inactive.');

            // Timestamp for when the record is created or updated
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_funds');
    }
};
