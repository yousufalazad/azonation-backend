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
            $table->id(); // Primary key

            // Foreign key linking to the users table (user who created the meeting)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove related meetings

            $table->string('name'); // Meeting name (required)
            $table->string('short_name')->nullable(); // Optional short name for the meeting
            $table->string('subject')->nullable(); // Optional subject of the meeting

            // Date and time for the meeting
            $table->date('date')->nullable(); // Meeting date (optional)
            $table->time('time')->nullable(); // Meeting time (optional)
            
            // Additional optional fields for meeting details
            $table->text('description')->nullable(); // Meeting description
            $table->string('address')->nullable(); // Meeting address, if in-person
            $table->text('agenda')->nullable(); // Meeting agenda details
            $table->text('requirements')->nullable(); // Special requirements for the meeting
            $table->text('note')->nullable(); // Additional notes about the meeting

            // Status of the meeting and conduct type (in-person, remote, hybrid)
            $table->tinyInteger('status')->default(0)->comment('0=Inactive, 1=Active')->nullable();
            $table->tinyInteger('conduct_type')->default(0)->comment('0=None, 1=In-person, 2=Remote, 3=Hybrid')->nullable();

            $table->timestamps(); // Created at and updated at timestamps
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
