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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_id'); // Foreign key to org table
            $table->string('name');
            $table->string('name_for_admin')->nullable();
            $table->string('subject')->nullable();
            $table->date('date')->nullable();
            $table->date('time')->nullable();
            $table->string('description')->nullable();
            $table->string('address')->nullable();
            $table->string('agenda')->nullable();
            $table->string('requirements')->nullable();
            $table->string('note')->nullable();
            $table->tinyInteger('status')->nullable()->default(0);
            $table->tinyInteger('conduct_type')->nullable()->default(0); //0=null, 1=in_person, 2=remote, 3=hybrid
            $table->timestamps();

            // Define foreign key constraint
           $table->foreign('org_id')->references('id')->on('organisations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
