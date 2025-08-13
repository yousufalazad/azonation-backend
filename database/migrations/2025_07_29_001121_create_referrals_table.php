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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_code_id')
                ->nullable()
                ->constrained('referral_codes')
                ->onDelete('cascade')
                ->comment('The referral code that was used');

            $table->foreignId('referrer_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The user who shared the referral code');

            $table->foreignId('referred_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('The user who signed up using the referral code (nullable until signup completes)');

            $table->string('email')->nullable()
                ->comment('Email entered at signup before referred user is created');

            $table->string('referral_source')->nullable()
                ->comment('How the user heard about us (e.g., friend, search, social)');

            $table->string('notes')->nullable()
                ->comment('Optional free text note or description about this referral');

            $table->ipAddress('ip_address')->nullable()
                ->comment('IP address of the referred user at the time of signup');

            $table->text('user_agent')->nullable()
                ->comment('User agent string of the browser/device used at signup');

            $table->boolean('signup_completed')->default(false)
                ->comment('Indicates whether the referred user completed their signup');

            $table->boolean('reward_given')->default(false)
                ->comment('Indicates whether the referral reward has been given');

            $table->timestamp('rewarded_at')->nullable()
                ->comment('When the reward was given for this referral');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
