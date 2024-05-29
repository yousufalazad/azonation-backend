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
        Schema::create('guest_meeting_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id'); // Foreign key to meetings table
            $table->string('guest_name')->nullable();
            $table->string('description')->nullable();
            $table->tinyInteger('attendance_type')->nullable()->default(0); //0=null, 1=in_person, 2=remote, 3=hybrid
            $table->date('date')->nullable();
            $table->date('time')->nullable();
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
        Schema::dropIfExists('guest_meeting_attendances');
    }
};
