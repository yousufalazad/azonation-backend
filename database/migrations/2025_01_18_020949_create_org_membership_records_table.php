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
        Schema::create('org_membership_records', function (Blueprint $table) {
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

            $table->string('existing_org_membership_id')->nullable()->comment('Existing organization membership identifier');

            // Foreign key to the membership_types table
            $table->foreignId('membership_type_id')
                  ->constrained('membership_types')
                  ->nullable()
                  ->onDelete('cascade')
                  ->comment('Foreign key to the membership_types table');

            $table->date('membership_start_date')->nullable()->comment('Date when the individual joined the organization');
            $table->date('membership_end_date')->nullable()->comment('Date when the individual left the organization');
            
            // Foreign key to the membership_impact_reasons table
            $table->foreignId('membership_impact_reason_id')
                  ->constrained('membership_impact_reasons')
                  ->nullable()
                  ->onDelete('cascade')
                  ->comment('Foreign key to the membership_impact_reasons table');

            // Foreign key to the users table representing the individual
            $table->foreignId('sponsored_user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('Foreign key to the users table representing the individual');
            
            $table->boolean('is_active')->default(true); // Status of the member (active or inactive)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_membership_records');
    }
};
