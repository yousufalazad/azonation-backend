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
        Schema::create('org_recognitions', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key referencing the users table (recipient of the recognition)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated recognitions

            // recognition details
            $table->string('title'); // Title of the recognition
            $table->text('description')->nullable(); // Description of the recognition
            $table->date('recognition_date'); // Date the recognition was given
            // Status of the recognition

                //Privacy like private, public, only member etc
            $table->foreignId('privacy_setup_id')
                ->constrained('privacy_setups')
                ->onDelete('cascade'); // Cascade on delete to remove associated recognitions

            $table->boolean('status')->default(1)->comment('yes = published, no = unpublished');

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_recognitions');
    }
};
