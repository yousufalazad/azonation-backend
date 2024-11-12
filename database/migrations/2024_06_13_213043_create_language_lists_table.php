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
        Schema::create('language_lists', function (Blueprint $table) {
            $table->id();

            // Full name of the language (e.g., 'English', 'French')
            $table->string('language_name')
                  ->comment('Full name of the language (e.g., English, French)');

            // ISO code for the language (e.g., 'en' for English)
            $table->string('language_code')
            ->unique()
            ->comment('ISO 639-1 language code (e.g., en, fr)');

            // Indicates if this language is the default language
            $table->boolean('default')->default(false)
                  ->comment('Indicates if this language is the default language');

            $table->boolean('is_active')->default(true); // Indicates whether the language_lists is active or not

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_lists');
    }
};
