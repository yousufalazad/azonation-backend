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

            // Scope: which org and which (current/last known) users
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Organisation user');

            $table->foreignId('individual_type_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Individual user (nullable to preserve record if user is removed)');

            // Snapshots at time of leaving (denormalised, survives user deletion)
            $table->string('terminated_member_name', 100)->nullable()
                ->comment('Full name at time of leaving');
            $table->string('terminated_member_email', 191)->nullable()
                ->comment('Email at time of leaving');
            $table->string('terminated_member_mobile', 32)->nullable()
                ->comment('Mobile at time of leaving');

            // Dates
            $table->date('terminated_at')->nullable()
                ->comment('Date the member left the organisation');
            $table->date('processed_at')->nullable()
                ->comment('Date the termination was processed');
            $table->date('joined_at')->nullable()
                ->comment('Original join date (for duration calc)');

            // Reason
            $table->foreignId('membership_termination_reason_id')
                ->constrained('membership_termination_reasons')
                ->cascadeOnDelete()
                ->comment('Reason for termination');

            // Who processed it (org admin user)
            $table->foreignId('org_administrator_id')
                ->nullable()
                ->constrained('org_administrators')
                ->nullOnDelete()
                ->comment('Org administrator who processed the termination');

            // Flags & details
            $table->boolean('rejoin_eligible')->default(true)
                ->comment('Eligible to rejoin in future');

            // Evidence / docs
            $table->string('file_path', 2048)->nullable()
                ->comment('Supporting document path/URL');

            // Snapshots (optional but useful; consider FKs if you want strictness)
            $table->unsignedInteger('membership_duration_days')->nullable()
                ->comment('Total membership duration in days');

            $table->string('membership_status_before_termination', 50)->nullable()
                ->comment('Membership status before termination (snapshot label)');
                
            $table->string('membership_type_before_termination', 100)->nullable()
                ->comment('Membership type before termination (snapshot label)');

            // Notes
            $table->string('org_note', 255)->nullable()
                ->comment('Organisation note on reason/context');

            $table->timestamps();

            // Helpful indexes (named to avoid >64 char limits)
            $table->index(['org_type_user_id', 'terminated_at'], 'idx_term_org_date');
            $table->index(['individual_type_user_id'], 'idx_term_individual');
            $table->index(['org_member_id'], 'idx_term_org_member');
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
