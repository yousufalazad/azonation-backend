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
        Schema::create('unlink_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Foreign key: The user');

            $table->string('existing_membership_id')->nullable()->comment('Existing organization membership identifier');

            // Foreign key to the membership_types table
            $table->foreignId('membership_type_id')
                ->nullable()
                ->constrained('membership_types')
                ->onDelete('set null')
                ->comment('Foreign key to the membership_types table');

            $table->date('membership_start_date')->nullable()->comment('Date when the individual joined the organization');

            // -------------------------------------------------------------------
            $table->foreignId('membership_status_id')
                ->nullable()
                ->constrained('membership_statuses')
                ->onDelete('cascade')
                ->comment('Lifecycle status of membership');

            $table->string('first_name'); // Member's first name
            $table->string('last_name')->nullable(); // Member's last name
            $table->string('email')->nullable(); // Member's email address (optional)
            $table->string('mobile')->nullable(); // Member's mobile number (optional)
            $table->text('address')->nullable(); // Member's address (optional)
            $table->string('note')->nullable(); // Notes added by admin about the member
            $table->boolean('is_active')->default(true); // Status of the member (active or inactive)
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unlink_members');
    }
};
