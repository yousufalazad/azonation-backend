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
        Schema::create('org_members', function (Blueprint $table) {
            $table->id();

            // Foreign key to the users table representing the organization
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Foreign key to the users table representing the organization');

            // Foreign key to the users table representing the individual
            $table->foreignId('individual_type_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Foreign key to the users table representing the individual');

            $table->string('existing_membership_id')->nullable()->comment('Existing organization membership identifier');

            // Foreign key to the membership_types table
            $table->foreignId('membership_type_id')
                ->nullable()
                ->constrained('membership_types')
                ->onDelete('set null')
                ->comment('Foreign key to the membership_types table');

            $table->date('membership_start_date')->nullable()->comment('Date when the individual joined the organization');
            $table->date('membership_end_date')->nullable()->comment('Date when the individual left the organization');

            // Foreign key to the users table representing the individual
            $table->foreignId('sponsored_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Foreign key to the users table representing the individual');

            $table->boolean('is_active')->default(true)->comment('Status of the member active or inactive');

            $table->timestamps();

            // Add a unique constraint to prevent the same individual being added twice to the same organization
            $table->unique(['org_type_user_id', 'individual_type_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_members');
    }
};
