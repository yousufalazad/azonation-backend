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
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key to users table
            $table->bigInteger('azon_id')->nullable(); // Creates 'azon_id' column as a custom auto-incrementing column, like Azon ID
            $table->string('org_name');
            $table->string('short_description')->nullable();
            $table->tinyInteger('status')->nullable()->default(0);
            $table->timestamps();

           // Define foreign key constraint
           $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('organisations');
    }
};
