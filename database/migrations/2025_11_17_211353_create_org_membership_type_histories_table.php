<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// History of a member's membership type changes in an organisation.
// The table already existed in the database without a migration; this records it.
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('org_membership_type_histories')) {
            return;
        }
        Schema::create('org_membership_type_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_type_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('individual_type_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prev_member_type_id')->nullable()->constrained('membership_types')->onDelete('set null');
            $table->foreignId('new_membership_type_id')->nullable()->constrained('membership_types')->onDelete('set null');
            $table->date('previous_membership_type_start')->nullable()->comment('Start date of the previous membership type');
            $table->date('previous_membership_type_end')->nullable()->comment('End date of the previous membership type');
            $table->integer('prev_member_type_duration_days')->nullable()->comment('Duration in days the member held the previous type');
            $table->date('changed_at')->nullable()->comment('Timestamp of the type change');
            $table->string('reason', 255)->nullable()->comment('Reason for the type change');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_membership_type_histories');
    }
};
