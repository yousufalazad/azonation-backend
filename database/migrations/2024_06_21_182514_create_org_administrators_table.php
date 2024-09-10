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
             $table->foreignId('org_user_id')
             ->constrained('users')
             ->onDelete('cascade'); // Cascade on delete to remove associated addresses when user is deleted

             $table->foreignId('individual_user_id')
             ->constrained('users')
             ->onDelete('cascade'); 
            $table->date('from_date')->nullable();
            $table->date('end_date')->nullable(); //Automatic date capture when change to new individual ID, can't editable (PUT not considerable), always new created
            $table->tinyInteger('status')->nullable()->default(1);
            $table->timestamps();
                });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_administrators');
    }
};
