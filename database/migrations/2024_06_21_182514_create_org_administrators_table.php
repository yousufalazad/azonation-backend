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
            $table->unsignedBigInteger('org_id'); // Foreign key to org table
            $table->unsignedBigInteger('individual_id'); // Foreign key to users table
            $table->date('from_date')->nullable();
            $table->date('end_date')->nullable(); //Automatic date capture when change to new individual ID, can't editable (PUT not considerable), always new created
            $table->tinyInteger('status')->nullable()->default(1);
            $table->timestamps();

           // Define foreign key constraint
           $table->foreign('org_id')->references('id')->on('organisations')->onDelete('cascade');
           $table->foreign('individual_id')->references('id')->on('individuals');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_administrators');
    }
};
