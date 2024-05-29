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
        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id'); // Foreign key to meetings table
            $table->string('minutes')->nullable();
            $table->string('decisions')->nullable();
            $table->string('note')->nullable();
            $table->tinyInteger('status')->nullable()->default(0);
            $table->timestamps();

            // Define foreign key constraint
           $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_minutes');
    }
};
