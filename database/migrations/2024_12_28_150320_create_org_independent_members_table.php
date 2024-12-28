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
        Schema::create('org_independent_members', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Member's full name
            $table->string('email')->nullable(); // Member's email address (optional)
            $table->string('mobile')->nullable(); // Member's mobile number (optional)
            $table->text('address')->nullable(); // Member's address (optional)
            $table->string('image_path')->nullable()->comment('Non-user member profile image URL');
            $table->string('admin_note')->nullable(); // Notes added by admin about the member
            $table->boolean('is_active')->default(true); // Status of the member (active or inactive)
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_independent_members');
    }
};
