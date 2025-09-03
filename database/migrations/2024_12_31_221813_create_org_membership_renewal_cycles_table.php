<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('org_membership_renewal_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Organisation user');

            $table->foreignId('member_renewal_cycle_id')
                ->constrained('membership_renewal_cycles')
                ->onDelete('cascade')
                ->comment('Relation to membership renewal cycle');

            $table->enum('alignment', ['member_anniversary', 'org_fiscal', 'calendar'])->default('member_anniversary')
                ->comment('How the renewal cycle aligns: member anniversary, organization fiscal year, or calendar year');

            $table->unsignedTinyInteger('anchor_month')->nullable()
                ->comment('1–12')
                ->check('anchor_month BETWEEN 1 AND 12');

            $table->unsignedTinyInteger('anchor_day')->nullable()
                ->comment('1–31')
                ->check('anchor_day BETWEEN 1 AND 31');

            $table->unsignedTinyInteger('anchor_weekday')->nullable()
                ->comment('1=Mon … 7=Sun')
                ->check('anchor_weekday BETWEEN 1 AND 7');

            $table->boolean('use_last_day_of_month')->default(false)->comment('Whether to use the last day of the month for anchor dates');
            $table->string('timezone')->default('Europe/London')->comment('Timezone for date calculations');
            $table->enum('proration_policy', ['none', 'daily', 'monthly'])->default('daily')->comment('Proration policy for mid-cycle changes');
            $table->unsignedSmallInteger('grace_days')->default(0)->comment('Number of grace days after membership expiration');

            $table->boolean('is_active')->default(true)->comment('Indicates whether this renewal cycle is active or not');

            $table->timestamps();

            $table->unique(['org_type_user_id', 'member_renewal_cycle_id'], 'org_membership_renewal_unique');
            $table->index(['org_type_user_id', 'is_active']);
            $table->index(['alignment', 'is_active']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('org_membership_renewal_cycles');
    }
};
