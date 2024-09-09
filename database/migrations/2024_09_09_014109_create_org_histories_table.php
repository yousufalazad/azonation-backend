<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'org_histories' table.
     */
    public function up(): void
    {
        Schema::create('org_histories', function (Blueprint $table) {
            $table->id(); // Primary key

            // Foreign key to the users table (organization owner)
            $table->foreignId('user_id')
                ->unique() // Ensures one history per organization
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated history

            // Organization history details
            $table->string('title')->nullable(); // Optional title for the history
            $table->longText('history')->nullable(); // Detailed history of the organization

            // Status of the history record
            $table->tinyInteger('status')->default(0)->comment('0 = inactive, 1 = active');

            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     * Drops the 'org_histories' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_histories');
    }
};
