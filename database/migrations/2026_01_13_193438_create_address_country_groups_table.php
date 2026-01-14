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
        Schema::create('address_country_groups', function (Blueprint $table) {
            $table->id();
             // Foreign key to the address_groups table
            $table->foreignId('address_group_id')
                ->constrained()
                ->onDelete('cascade')
                ->unique() // Ensure that each address_group_id can only appear once
                ->comment('Foreign key linking to the address_groups table');
            // Foreign key to the countries table
            $table->foreignId('country_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Foreign key linking to the countries table');

            $table->string('address_group_alias', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['address_group_id', 'country_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_country_groups');
    }
};
