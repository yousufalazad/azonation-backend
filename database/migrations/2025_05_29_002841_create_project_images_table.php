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
        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade')
                ->comment('Image path relational table with projects table.');

            $table->string('image_path')->comment('The storage path or URL of the profile image');
            $table->string('file_name')->nullable()->comment('The original file name of the uploaded image');
            $table->string('mime_type')->nullable()->comment('The MIME type of the image file, e.g., image/jpeg or image/png');
            $table->integer('file_size')->nullable()->comment('The size of the image file in kilobytes (KB)');
            $table->boolean('is_public')->default(true)->comment('Whether the profile image is publicly visible or not');
            $table->boolean('is_active')->default(true); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_images');
    }
};
