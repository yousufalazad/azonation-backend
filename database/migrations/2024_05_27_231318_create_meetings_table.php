<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create the 'meetings' table.
     */
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id()->comment('Primary key'); // Primary key

            // Foreign key linking to the users table
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('User who created the meeting');

            $table->string('name')->comment('Meeting name'); // Meeting name (required)
            $table->string('short_name')->nullable()->comment('Optional short name for the meeting');
            $table->string('subject')->nullable()->comment('Optional subject of the meeting');

            // Date and time for the meeting
            $table->date('date')->nullable()->comment('Meeting date'); // Meeting date
            $table->time('start_time')->nullable()->comment('Meeting start time');
            $table->time('end_time')->nullable()->comment('Meeting end time');

            // Meeting enhancements
            $table->string('meeting_type')->nullable()->comment('Type of meeting (e.g., Board Meeting, Workshop)');
            $table->string('timezone')->nullable()->comment('Time zone for the meeting');
            $table->string('meeting_mode')->nullable()->comment('Mode of the meeting (In-person, Virtual, Hybrid)');
            $table->integer('duration')->nullable()->comment('Duration of the meeting in minutes');
            $table->string('priority')->nullable()->comment('Priority of the meeting (High, Medium, Low)');

            // Virtual meeting features
            $table->string('video_conference_link')->nullable()->comment('Link for virtual meetings');
            $table->string('access_code')->nullable()->comment('Secure access code for virtual meetings');
            $table->string('recording_link')->nullable()->comment('Link to the meeting recording');
            $table->string('meeting_host')->nullable()->comment('Name or email of the meeting host');

            // Participant management
            $table->integer('max_participants')->nullable()->comment('Maximum number of participants allowed');
            $table->text('rsvp_status')->nullable()->comment('JSON to track participant RSVP responses');
            $table->text('participants')->nullable()->comment('JSON with participant details like name and email');

            // Additional optional fields for meeting details
            $table->text('description')->nullable()->comment('Description of the meeting');
            $table->string('address')->nullable()->comment('Address of the meeting if in-person');
            $table->text('agenda')->nullable()->comment('Detailed agenda of the meeting');
            $table->text('requirements')->nullable()->comment('Special requirements for the meeting');
            $table->text('note')->nullable()->comment('Additional notes about the meeting');
            $table->text('tags')->nullable()->comment('Tags for categorisation, stored as JSON');

            // Recurrence and reminders
            $table->integer('reminder_time')->nullable()->comment('Reminder time in minutes before the meeting starts');
            $table->string('repeat_frequency')->nullable()->comment('Recurrence pattern (e.g., Daily, Weekly)');

            // Attachments
            $table->string('attachment')->nullable()->comment('File path or URL for meeting resources');

            // Foreign key linking to conduct types
            $table->foreignId('conduct_type_id')
                ->nullable()
                ->constrained('conduct_types')
                ->onDelete('set null')
                ->comment('Type of conduct (e.g., Formal, Informal)');

            // Status and metadata
            $table->boolean('is_active')
                ->default(1)
                ->nullable()
                ->comment('Active status: 0=Inactive, 1=Active');

            //meeting privacy settings for 
            $table->foreignId('privacy_setup_id')
                ->constrained('privacy_setups')
                ->nullable()
                ->onDelete('set null')
                ->comment('Meeting visibility (Public, Private, Members Only ect)');

            $table->text('cancellation_reason')->nullable()->comment('Reason for cancelling the meeting');
            $table->string('feedback_link')->nullable()->comment('Link for submitting meeting feedback');

            // Traceability
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User who created the meeting (traceability)');

            // Traceability
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User who updated the meeting (traceability)');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Drop the 'meetings' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
