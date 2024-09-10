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
        Schema::create('org_events', function (Blueprint $table) {
            $table->id();
            // Foreign key to 'users' table
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated addresses when user is deleted


            $table->string('title');
            $table->string('name')->nullable();
            $table->string('short_description')->nullable();
            $table->string('description')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('requirements')->nullable();
            $table->string('note')->nullable();
            $table->tinyInteger('status')->nullable()->default(0);
            $table->tinyInteger('conduct_type')->nullable()->default(0); //0=null, 1=in_person, 2=remote, 3=hybrid
            $table->timestamps();

          
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_events');
    }
};
