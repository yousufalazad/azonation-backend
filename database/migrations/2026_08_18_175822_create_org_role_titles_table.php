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
        Schema::create('org_role_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_type_user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Foreign key: The user representing the organization type');
            $table->string('name')->comment('Name of the role title');

            // prevent duplicate titles inside same organisation
            $table->unique(['org_type_user_id', 'name']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_role_titles');
    }
};