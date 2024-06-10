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
        Schema::create('org_member_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_id'); // Foreign key to org table
            $table->unsignedBigInteger('individual_id'); // Foreign key to users table
            $table->string('existing_org_membership_id')->nullable();
            $table->tinyInteger('membership_type')->nullable()->default(0);
            $table->date('joining_date')->nullable();
            $table->date('end_date')->nullable();
            $table->tinyInteger('status')->nullable()->default(0);
            $table->timestamps();

           // Define foreign key constraint
           $table->foreign('org_id')->references('id')->on('organisations')->onDelete('cascade');
           $table->foreign('individual_id')->references('id')->on('individuals');
           $table->foreign('membership_type')->references('id')->on('org_membership_type_lists');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_member_lists');
    }
};
