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
        Schema::create('storage_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->comment('Name of the package (e.g., Starter, Essentials, Professional, Enterprise)');
            $table->string('slug', 100)->unique()->comment('Unique slug for URL and identification purposes');
            $table->string('description', 255)->nullable()->comment('Optional description of the package');
            $table->integer('storage_max_limit')->default(1024)->comment('Storage limit in MB (default 1024MB)');
            $table->boolean('is_storage_grace_period_allow')->default(false)->comment('Enable grace period for storage limit');
            $table->boolean('is_over_use_allow')->default(false)->comment('Enable over use of storage limit');

            // is_active column to indicate if the package is currently active or not
            $table->boolean('is_active')->default(false)->comment('Indicates if the package is active or not');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_packages');
    }
};
