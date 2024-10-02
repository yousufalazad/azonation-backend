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
        Schema::create('membership_types', function (Blueprint $table) {
            $table->id();

            // Name of the membership type (e.g., Honorary Member, Lifetime Member, General Member)
            $table->string('name')
                  ->unique()
                  ->comment('Unique name representing the membership type: Honorary Member, Lifetime Member, General Member, Associate Member, Temporary Member, Prospective Member, Not A Member');

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_types');
    }
};
