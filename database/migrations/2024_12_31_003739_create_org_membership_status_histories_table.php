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
        Schema::create('org_membership_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_member_id')
                ->constrained('org_members')
                ->onDelete('cascade')
                ->comment('Reference to the organization member');

            $table->foreignId('previous_membership_status_id')
                ->nullable()
                ->constrained('membership_statuses')
                ->onDelete('set null')
                ->comment('Previous membership status');

            $table->foreignId('new_membership_status_id')
                ->nullable()
                ->constrained('membership_statuses')
                ->onDelete('set null')
                ->comment('New membership status');

            $table->integer('previous_status_duration_days')->nullable()->comment('Duration in days the member held the previous status');

            $table->date('changed_at')->nullable()->comment('Timestamp of the status change');
            $table->string('reason', 255)->nullable()->comment('Reason for the status change');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_membership_status_histories');
    }
};
