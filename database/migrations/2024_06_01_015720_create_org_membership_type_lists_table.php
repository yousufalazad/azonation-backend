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
        Schema::create('org_membership_type_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_id'); // Foreign key to org table
            $table->unsignedBigInteger('individual_id'); // Foreign key to users table
            $table->tinyInteger('type')->nullable()->default(0); //0 = null, 1=Honorary Member, 2=Lifetime Member, 3=General Member, 4=Associate Member, 5=Temporary Member, 6=Prospective Member, 7=Not A Member
            $table->timestamps();

           // Define foreign key constraint
           $table->foreign('org_id')->references('id')->on('organisations')->onDelete('cascade');
           $table->foreign('individual_id')->references('id')->on('individuals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_membership_type_lists');
    }
};
