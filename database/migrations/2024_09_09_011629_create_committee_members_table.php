<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('committee_id')
                ->constrained('committees')
                ->onDelete('cascade');

            $table->foreignId('user_id') //for individual type user
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('designation_id')
                ->constrained('designations');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('note')->nullable();
            $table->tinyInteger('is_active')->nullable()->default(1);
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('committee_members');
    }
};
