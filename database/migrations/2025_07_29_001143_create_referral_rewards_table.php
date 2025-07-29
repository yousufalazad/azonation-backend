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
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')
                ->constrained('referrals')
                ->onDelete('cascade')
                ->comment('Linked referral that triggered this reward');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('User who received the reward');

            $table->string('reward_type')->nullable()->comment('Type of reward: credit, discount, etc.');
            $table->decimal('amount', 10, 2)->nullable()->comment('Value of the reward');
            $table->enum('status', ['pending', 'approved', 'cancelled'])
                ->default('pending')
                ->comment('Reward status');

            $table->timestamp('rewarded_at')->nullable()->comment('Timestamp when reward was granted');
            $table->text('notes')->nullable()->comment('Optional notes about the reward');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
    }
};
