<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('org_administrators', function (Blueprint $table) {
            $table->id();

            // Foreign key to 'users' table
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated addresses when user is deleted

            // Foreign key to 'users' table for individual users
            $table->foreignId('individual_type_user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable(); // Optional last name for the individual user
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable(); //Automatic date capture when change to new individual ID, can't editable (PUT not considerable), always new created
            $table->string('admin_note', 255)->nullable(); // Admin notes, optional field
            $table->boolean('is_primary')->nullable()->default(1); // Indicates if this is the primary administrator
            $table->boolean('is_active')->nullable()->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_administrators');
    }
};
