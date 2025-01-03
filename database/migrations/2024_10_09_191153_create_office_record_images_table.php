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
        Schema::create('office_record_images', function (Blueprint $table) {
            $table->id();
            // Foreign key referencing the users table (creator or recipient of the plan)
            $table->foreignId('office_record_id')
                ->constrained('office_records')
                ->onDelete('cascade')
                ->comment('Image path relational table record.');

            $table->string('image', 255);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_record_images');
    }
};
