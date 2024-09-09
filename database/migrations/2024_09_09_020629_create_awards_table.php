<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'awards' table.
     */
    public function up(): void
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key referencing the users table (recipient of the award)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated awards

            // Award details
            $table->string('title'); // Title of the award
            $table->text('description')->nullable(); // Description of the award
            $table->date('award_date'); // Date the award was given
            // Status of the award
            $table->tinyInteger('status')->default(1)->comment('0 = inactive, 1 = active');

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'awards' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};
