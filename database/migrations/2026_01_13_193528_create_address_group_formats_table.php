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
        Schema::create('address_group_formats', function (Blueprint $table) {
            $table->id();
            // Foreign key to the address_groups table
            $table->foreignId('address_group_id')
                ->constrained()
                ->onDelete('cascade')
                ->unique() // Ensure that each address_group_id can only appear once
                ->comment('Foreign key linking to the address_groups table');
            $table->string('address_group_alias', 30)->nullable();
            $table->json('format_components')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_group_formats');
    }
};