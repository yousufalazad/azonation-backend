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
        Schema::create('privacy_setups', function (Blueprint $table) {
            $table->id();
            
            // Privacy type (e.g., Private, Public, Only for Members)
            $table->string('name')->unique()->comment('The name of the privacy setting, e.g., Private, Public, Only for Members');
            
            // Description of the privacy type
            $table->text('description')->nullable()->comment('Detailed description of the privacy setting.');
            
            // Whether this privacy option is available globally for all organizations
            $table->boolean('status')->default(true)->comment('Indicates if the this privacy setting is globally available or not available in the system.');

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privacy_setups');
    }
};
