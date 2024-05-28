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
        Schema::create('meeting_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id'); // Foreign key to org table
            $table->boolean('in_app_notification')->nullable();
            $table->boolean('app_inbox')->nullable();
            $table->boolean('email')->nullable();
            $table->boolean('mobile_sms')->nullable();
            $table->boolean('whatsApp')->nullable();
            $table->boolean('other')->nullable();
            $table->date('date')->nullable();
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
        Schema::dropIfExists('meeting_notifications');
    }
};
