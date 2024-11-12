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
        Schema::create('user_countries', function (Blueprint $table) {
            $table->id();

            // Foreign key to the users table
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->unique() // Ensure that each user_id can only appear once
                ->comment('Foreign key linking to the users table');

            // Foreign key to the countries table
            $table->foreignId('country_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Foreign key linking to the countries table');
            $table->boolean('is_active')->default(true); // Indicates whether the user-country relationship is active or not

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_countries');
    }
};
