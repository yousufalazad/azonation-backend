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
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->comment('Foreign key: Links to the products table');
            
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Foreign key: Links to the users table (nullable for guest reviews)');
        
            $table->tinyInteger('rating')->unsigned()->comment('Rating given by the user (1-5 stars)');
            $table->text('review')->nullable()->comment('The review content');
            $table->boolean('approved')->default(false)->comment('Indicates if the review is approved by admin');
            $table->boolean('is_active')->default(true)->comment('Whether the product_reviews is active or inactive');

            $table->timestamps();
        
            // Add a unique constraint to prevent duplicate reviews by the same user for the same product
            $table->unique(['product_id', 'user_id'], 'unique_user_product_review');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
