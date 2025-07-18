<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'founders' table.
     */
    public function up(): void
    {
        Schema::create('founders', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key referencing the users table (for organization)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade') // Cascade on delete to remove associated founders
                ->comment('Organisation ID');

            $table->foreignId('founder_user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade') // Cascade on delete to remove associated founders 
                ->comment('Individual ID');

            // Founder details
            //$table->string('name')->nullable(); // need to change to full_name
            $table->string('full_name', 100)->nullable(); // name of the founder
            $table->string('email', 50)->nullable(); // email of the founder
            $table->string('mobile', 20)->nullable(); // mobile number of the founder
            $table->string('address', 255)->nullable(); // address of the founder
            $table->string('note', 255)->nullable(); // additional notes about the founder
            
            $table->string('designation')->nullable(); // designation of the founder
            $table->boolean('is_active')->default(true); // Active status

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'founders' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('founders');
    }
};
