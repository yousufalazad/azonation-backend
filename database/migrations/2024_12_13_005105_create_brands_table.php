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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('The name of the brand');
            $table->text('description')->nullable()->comment('A brief description of the brand');
            $table->string('logo_path')->nullable()->comment('The URL or path to the brand logo image');
            $table->boolean('is_active')->default(true)->comment('Whether the brand is active or inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
