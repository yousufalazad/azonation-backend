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
        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->nullable()
                ->comment('Foreign key: Links to the products table');
            $table->string('media_type')->nullable()->comment('Type of media (e.g., image, video)');
            $table->string('file_path')->nullable()->comment('Path to the media file');
            $table->string('file_name')->nullable()->comment('Original file name');
            $table->string('file_extension')->nullable()->comment('File extension (e.g., jpg, mp4)');
            $table->string('title')->nullable()->comment('Title for the media file');

            $table->string('alt_text')->nullable()->comment('Alternative text for accessibility and SEO');
            $table->integer('sort_order')->nullable()->comment('Sort order for displaying multiple images');
            $table->boolean('is_primary')->default(false)->comment('Flag to indicate if this is the primary image for the product');
            
            $table->unsignedBigInteger('file_size')->nullable()->comment('File size in bytes');
            $table->boolean('is_active')->default(true)->comment('Whether the product_media is active or inactive');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_media');
    }
};
