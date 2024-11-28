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
            
            $table->boolean(column: 'is_active')->default(true)->comment('Whether the privacy setting is active'); 

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
