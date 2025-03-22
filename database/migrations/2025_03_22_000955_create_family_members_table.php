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
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Organisation primary ID');
            $table->foreignId('member_id')->constrained('users')->onDelete('cascade')->comment('main member id associated with this organisation'); // Main member ID
            $table->string('name');
            $table->date('day_month_of_birth')->nullable()->comment('Day and month of birth');
            $table->enum('gender', ['male', 'female', 'other']); // Relationship with main member
            $table->enum('relationship', ['child', 'spouse', 'sibling', 'dependent', 'other']); // Relationship with main member
            $table->enum('life_status', ['active', 'deceased', 'left']); // Status of the member
            $table->boolean('is_active')->default('true')->comment('Is this member active?');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
