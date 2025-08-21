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
        // Create the users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('azon_id')->unique()->nullable()->comment('Unique ID from an external system');
            $table->enum('type', ['individual', 'organisation', 'superadmin', 'guest'])->default('guest')->comment('Type of user account');
            $table->string('first_name', 30)->nullable()->comment('First name of the user');
            $table->string('last_name', 30)->nullable()->comment('Last name of the user');
            $table->string('org_name', 200)->nullable()->comment('Organisation name');
            $table->string('username', 30)->unique()->nullable()->comment('Unique username for the user');
            $table->string('email')->unique()->comment('Email address');
            $table->uuid('verification_token')->nullable()->unique()->comment('Email verification token');
            $table->timestamp('email_verified_at')->nullable()->comment('Email verification timestamp');
            $table->string('password')->comment('Password hash');
            $table->rememberToken()->comment('Token for "remember me" functionality');
            $table->string('reset_code')->nullable();
            $table->timestamp('reset_code_expires_at')->nullable();
            $table->enum('activation_status', [
                'active', 
                'inactive', 
                'pending', 
                'hold', 
                'under_review', 
                'suspended', 
                'banned'
            ])->default('active')
              ->comment('User activation status: active, inactive, pending, hold, under review, suspended, or banned. Superadmin can modify if necessary.');
            
            $table->timestamps();
        });

        // Create the password_reset_tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary()->comment('Email address associated with the reset token');
            $table->string('token')->comment('Reset token');
            $table->timestamp('created_at')->nullable()->comment('Token creation timestamp');
        });

        // Create the sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary()->comment('Session ID');
            $table->foreignId('user_id')->nullable()->index()->constrained()->onDelete('cascade')->comment('Foreign key to users table');
            $table->string('ip_address', 45)->nullable()->comment('IP address of the user');
            $table->text('user_agent')->nullable()->comment('User agent string');
            $table->longText('payload')->comment('Session data');
            $table->integer('last_activity')->index()->comment('Timestamp of last activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order of creation
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
