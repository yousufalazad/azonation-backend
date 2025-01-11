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
        Schema::create('success_story_files', function (Blueprint $table) {
            $table->id();

            // Foreign key referencing the office_records table
            $table->foreignId('success_story_id')
                ->constrained('success_stories')
                ->onDelete('cascade')
                ->comment('success_stories primary id');
            
            $table->string('file_path')->comment('The storage path or URL of the document file');
            $table->string('file_name')->nullable()->comment('The original file name of the uploaded document');
            $table->string('mime_type')->nullable()->comment('The MIME type of the document file, e.g., application/pdf, application/msword, application/vnd.ms-excel');
            $table->integer('file_size')->nullable()->comment('The size of the document file in kilobytes (KB)');
            $table->boolean('is_public')->default(true)->comment('Whether the document is publicly visible or not');
            $table->boolean('is_active')->default(true)->comment('Status for active/inactive document');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('success_story_files');
    }
};
