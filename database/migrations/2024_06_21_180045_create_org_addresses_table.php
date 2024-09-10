<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method is responsible for creating the 'org_addresses' table. 
     * It includes a foreign key reference to the 'users' and 'country_names' tables.
     */
    public function up(): void
    {
        Schema::create('org_addresses', function (Blueprint $table) {
            $table->id();

            // Foreign key to 'users' table
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Cascade on delete to remove associated addresses when user is deleted

            // Address fields
            $table->string('address_line_one')->nullable(); // Nullable for cases where the address might be incomplete
            $table->string('address_line_two')->nullable(); // Nullable for cases where the address might be incomplete
            $table->string('city')->nullable();         // City, can be null
            $table->string('state_or_region')->nullable(); // State or region, can be null
            $table->string('postal_code')->nullable();  // Postal code, can be null

            // Foreign key to 'country_names' table
            $table->foreignId('country_id')
                ->constrained('country_names')
                ->onDelete('cascade'); // Cascade on delete to handle associated addresses upon country deletion

            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method is responsible for dropping the 'org_addresses' table if the migration is rolled back.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_addresses');
    }
};
