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
        Schema::create('individual_countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('individual_id'); // Foreign key to individuals table
            $table->unsignedBigInteger('country_id')->nullable();
            $table->timestamps();

            // Define foreign key constraint
           $table->foreign('individual_id')->references('id')->on('individuals')->onDelete('cascade');
           $table->foreign('country_id')->references('id')->on('country_names')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individual_countries');
    }
};
