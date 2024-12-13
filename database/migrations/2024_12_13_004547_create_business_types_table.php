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
        Schema::create('business_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Name of the business type (e.g., Azonation Subscriptions, Addons, Products, Services, Templates, etc.)');
            $table->text('description')->nullable()->comment('Optional: Detailed description of the business type');
            $table->string('business_type_image_path')->nullable()->comment('Path to the category image');

            $table->string('slug')->unique()->comment('URL-friendly version of the category name');
            $table->string('meta_description')->nullable()->comment('SEO description for the category');
            $table->integer('order')->default(0)->comment('Order of the business_types for display purposes');

            $table->boolean('is_active')->default(true)->comment('Whether the business_types is active or inactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_types');
    }
};
