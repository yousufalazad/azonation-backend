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
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); // Primary key
            
            // Foreign key referencing the users table (owner of the project)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated projects
            
            // Project details
            $table->string('title'); // Project title (required)
            $table->string('short_description')->nullable(); // Optional short description
            $table->text('description')->nullable(); // Optional full description

            // Project dates and times
            $table->date('start_date')->nullable(); // Optional start date
            $table->date('end_date')->nullable(); // Optional end date
            $table->time('start_time')->nullable(); // Optional start time
            $table->time('end_time')->nullable(); // Optional end time

            // Venue details for in-person projects
            $table->string('venue_name')->nullable(); // Optional venue name
            $table->string('venue_address')->nullable(); // Optional venue address

            // Additional optional fields
            $table->text('requirements')->nullable(); // Optional requirements for the project
            $table->text('note')->nullable(); // Optional additional notes

            // Status and conduct type (in-person, remote, hybrid)
            $table->tinyInteger('status')->default(0)->comment('0=Inactive, 1=Active')->nullable();
            $table->tinyInteger('conduct_type')->default(0)->comment('0=None, 1=In-person, 2=Remote, 3=Hybrid')->nullable();

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
