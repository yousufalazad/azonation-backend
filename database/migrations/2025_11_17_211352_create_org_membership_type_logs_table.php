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
        Schema::create('org_membership_type_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Reference to the users for organisation');

            $table->foreignId('individual_type_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Reference to the users for organisation member');

            $table->foreignId('membership_type_id')
                ->nullable()
                ->constrained('membership_types')
                ->onDelete('set null')
                ->comment('Previous membership type');

            $table->date('membership_type_start')->nullable()->comment('Start date of the previous membership type');
            $table->date('membership_type_end')->nullable()->comment('End date of the previous membership type');
        
            $table->integer('membership_type_duration_days')->nullable()->comment('Duration in days the member held the previous type');
            $table->date('changed_at')->nullable()->comment('Timestamp of the type change');
            $table->string('reason', 255)->nullable()->comment('Reason for the type change');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_membership_type_logs');
    }
};
