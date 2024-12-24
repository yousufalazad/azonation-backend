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
        Schema::create('profile_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->unique()
                ->cascadeOnDelete()
                ->comment('Foreign key: The user');

            $table->string('image_path')->nullable()->comment('The storage path or URL of the profile image');
            $table->string('image_type')->default('profile')->comment('The type of image: "profile" for personal profile photos, "logo" for organisation logos');
            $table->string('file_name')->nullable()->comment('The original file name of the uploaded image');
            $table->string('mime_type')->nullable()->comment('The MIME type of the image file, e.g., image/jpeg or image/png');
            $table->integer('file_size')->nullable()->comment('The size of the image file in kilobytes (KB)');

            $table->boolean('is_active')->default(true); // Status for active/inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_images');
    }
};
