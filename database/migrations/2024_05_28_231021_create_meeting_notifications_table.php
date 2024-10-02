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

            // Foreign key to the meetings table using foreignId and constrained
            $table->foreignId('meeting_id')
                  ->constrained('meetings')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete()
                  ->comment('Foreign key referencing the meetings table');

            // Notification channels
            $table->boolean('in_app_notification')->default(false)->comment('Whether an in-app notification is sent');
            $table->boolean('app_inbox')->default(false)->comment('Whether the notification is sent to app inbox');
            $table->boolean('email')->default(false)->comment('Whether an email notification is sent');
            $table->boolean('mobile_sms')->default(false)->comment('Whether an SMS notification is sent');
            $table->boolean('whatsapp')->default(false)->comment('Whether a WhatsApp notification is sent');
            $table->boolean('other')->default(false)->comment('Any other notification method');

            // Notification date
            $table->date('date')->nullable()->comment('The date the notification is sent');

            // Timestamps for created_at and updated_at
            $table->timestamps();
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
