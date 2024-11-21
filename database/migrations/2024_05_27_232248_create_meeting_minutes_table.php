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

            // Foreign key to meetings table
            $table->foreignId('meeting_id')
                ->constrained('meetings')
                ->cascadeOnDelete()
                ->comment('Foreign key referencing the meetings table');

            // meeting minutes prepared by users
            $table->foreignId('prepared_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who prepared the meeting minutes');

            // meeting minutes reviewed by users
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User who reviewed the meeting minutes');

            // Core meeting minutes fields
            $table->text('minutes')->nullable()->comment('Summary of minutes from the meeting');
            $table->text('decisions')->nullable()->comment('Decisions made during the meeting');
            $table->text('note')->nullable()->comment('Additional notes for the meeting');
            $table->string('file_attachments')->nullable()->comment('Related PDF files for the meeting');
            
            // time for the meeting
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            //optional meeting minutes fields
            $table->text('follow_up_tasks')->nullable()->comment('Tasks to follow up from the meeting');
            $table->string('tags')->nullable()->comment('Tags for categorisation and searchability');
            $table->text('action_items')->nullable()->comment('Action items with assignees and deadlines');
            $table->string('meeting_location')->nullable()->comment('Location of the meeting');
            $table->string('video_link')->nullable()->comment('URL link to the recorded meeting video');

            //meeting minutes privacy settings for members and admin
            $table->foreignId('privacy_setup_id')
            ->constrained('privacy_setups')
            ->onDelete('cascade')
            ->comment('Privacy level of the asset (e.g., public, private, only members. etc).');

            //Approval status from members
            $table->tinyInteger('approval_status')->default(0)->comment('Approval status: 0 = Pending, 1 = Approved, 2 = Rejected');
            
            //Publishing status
            $table->boolean('is_publish')->default(value: false)->comment('Is the meeting minutes published?');
            
            //General active or inactive status
            $table->boolean('is_active')->default(value: true)->comment('Is the meeting minutes active or inactive?');
            
            $table->timestamps();
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