<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create the 'org_profiles' table.
     */
    public function up(): void
    {
        Schema::create('org_profiles', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key referencing the users table (organization owner)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated profiles

            // Organization descriptions and details
            $table->string('short_description')->nullable(); // Optional short description of the organization
            $table->text('detail_description')->nullable(); // Detailed description of the organization
            $table->text('who_we_are')->nullable(); // Who the organization is
            $table->text('what_we_do')->nullable(); // What the organization does
            $table->text('how_we_do')->nullable(); // How the organization operates
            $table->text('mission')->nullable(); // Organization's mission
            $table->text('vision')->nullable(); // Organization's vision
            $table->text('value')->nullable(); // Organization's values
            $table->text('areas_of_focus')->nullable(); // Areas of focus for the organization
            $table->text('causes')->nullable(); // Causes the organization supports
            $table->text('impact')->nullable(); // The organization's impact
            $table->text('why_join_us')->nullable(); // Reasons to join the organization

            // Scope and important dates
            $table->tinyInteger('scope_of_work')->default(0)->comment('0 = local, 1 = national, 2 = international');
            $table->date('organising_date')->nullable(); // Date the organization started organizing
            $table->date('foundation_date')->nullable(); // Date the organization was officially founded

            // Status of the organization profile
            $table->tinyInteger('status')->default(0)->comment('0 = inactive, 1 = active');

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'org_profiles' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_profiles');
    }
};
