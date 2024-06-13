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
        Schema::create('phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_id'); // Foreign key to organisations table
            $table->unsignedBigInteger('country_code_id');
            $table->tinyInteger('phone_number');
            $table->tinyInteger('phone_type')->nullable()->default(0); //Type of phone number (1=Mobile, 2=Home, 3=Work)
            $table->boolean('status')->nullable(); // for verification status, yes/no
            $table->timestamps();

            // Define foreign key constraint
           $table->foreign('org_id')->references('id')->on('organisations')->onDelete('cascade');
           $table->foreign('country_code_id')->references('id')->on('country_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_numbers');
    }
};
