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
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('The user who owns and shares this referral code');

            $table->string('code')->unique()
                ->comment('The unique referral code string used by others to sign up');

            $table->string('reward_type')->nullable()
                ->comment('Type of reward the referrer earns (e.g., credit, discount)');

            $table->decimal('reward_value', 10, 2)->nullable()
                ->comment('Value of the reward given for each successful referral');

            $table->integer('max_uses')->nullable()
                ->comment('Maximum number of times this code can be used (null means unlimited)');

            $table->integer('times_used')->default(0)
                ->comment('How many times this code has been used so far');

            $table->enum('status', ['active', 'disabled', 'expired'])->default('active')
                ->comment('Current status of the referral code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_codes');
    }
};
