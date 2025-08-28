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
        Schema::create('membership_terminations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Organisation user');

            $table->foreignId('individual_type_user_id')
                ->constrained('users')
                ->softDelete('cascade')
                ->comment('Individual user who is a former member');
            
            $table->string('terminated_member_name', 60)->nullable()->comment('Former member full name at time of leaving');
            $table->string('terminated_member_email', 50)->nullable()->comment('Former member email at time of leaving');
            $table->string('terminated_member_mobile', 20)->nullable()->comment('Former member mobile number at time of leaving');
            $table->date('terminated_at')->nullable()->comment('Date when the user left the organisation');
            $table->date('processed_at')->nullable()->comment('Date when the termination was processed');
            $table->date('joined_at')->nullable()->comment('Date when the user joined the organisation');
            $table->foreignId('membership_termination_reason_id')
                ->constrained('membership_termination_reasons')
                ->onDelete('cascade')
                ->comment('Reason for membership termination');

            $table->foreignId('org_administrator_id')
                ->nullable()
                ->constrained('org_administrators')
                ->nullOnDelete()
                ->comment('Org administrator who processed the termination, primary id of org_administrators table');

            $table->boolean('rejoin_eligible')->default(true)->comment('Eligible to rejoin in future');
            
            $table->string('file_path')->nullable()->comment('Supporting document (resignation/termination proof)');
            $table->integer('membership_duration_days')->nullable()->comment('Total membership duration in days');
            $table->string('status_before_termination')->nullable()->comment('Membership status before termination');
            $table->string('membership_type_before_termination')->nullable()->comment('Membership type before termination');

            $table->string('org_note', 255)->nullable()->comment('Reason for leaving the organisation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_terminations');
    }
};
