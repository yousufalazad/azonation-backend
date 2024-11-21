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
        Schema::create('event_summaries', function (Blueprint $table) {
            $table->id();

            // Foreign keys with proper relationships
            $table->foreignId('org_event_id')
                ->constrained('org_events')
                ->cascadeOnDelete()
                ->comment('Foreign key referencing the org_events table');

            // Attendance details
            $table->integer('total_member_attendance')
                ->default(0)
                ->comment('Total number of members who attended the event');
            $table->integer('total_guest_attendance')
                ->default(0)
                ->comment('Total number of guests who attended the event');
            
            
            // Event content details
            $table->text('summary')->nullable()->comment('Summary or description of the event');
            $table->text('highlights')->nullable()->comment('Key highlights or notable moments of the event');
            $table->text('feedback')->nullable()->comment('Feedback from attendees or organisers');
            $table->text('challenges')->nullable()->comment('Challenges or issues faced during the event');
            $table->text('suggestions')->nullable()->comment('Suggestions or recommendations for future events');
            $table->text('financial_overview')->nullable()->comment('Summary of the event’s financial details (budget, expenses, etc.)');
            $table->integer('total_expense')
            ->default(0)
            ->comment('Total expense for the event');
            // Attachments
            $table->string('image_attachment')->nullable()->comment('Path to an image related to the event');
            $table->string('file_attachment')->nullable()->comment('Path to a file (PDF, DOC, etc.) related to the event');

            // Future steps
            $table->text('next_steps')->nullable()->comment('Follow-up actions or plans after the event');

            // Prepared and updated by users
            $table->string('created_by', 30)->nullable()->comment('User who created the event summary');
            $table->string('updated_by', 30)->nullable()->comment('User who last updated the event summary');

            // Privacy and status
            $table->foreignId('privacy_setup_id')
                ->constrained('privacy_setups')
                ->cascadeOnDelete()
                ->comment('Privacy level of the event summary (e.g., public, private, members only)');
            $table->boolean('is_active')
                ->default(true)
                ->comment('Indicates whether the event summary is active or inactive');
            $table->boolean('is_publish')
                ->default(false)
                ->comment('Indicates whether the event summary is published and visible to users');

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_summaries');
    }
};