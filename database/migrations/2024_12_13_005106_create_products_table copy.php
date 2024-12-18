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
        Schema::create('products', function (Blueprint $table) {
            $table->id();  // Unique ID for the product

            // Foreign Keys for Relationships
            $table->foreignId('business_type_id')  // Links to the categories table
                ->constrained('business_types')
                ->onDelete('cascade')
                ->comment('Foreign key: Links to the parent business_types');

            // Foreign Keys for Relationships
            $table->foreignId('category_id')  // Links to the categories table
                ->constrained('categories')
                ->onDelete('cascade')
                ->comment('Foreign key: Links to the parent category');

            $table->foreignId('sub_category_id')  // Links to the sub_categories table
                ->nullable()
                ->constrained('sub_categories')
                ->onDelete('set null')
                ->comment('Foreign key: Links to the subcategory (nullable)');


            $table->foreignId('sub_sub_category_id')  // Links to the sub_categories table
                ->nullable()
                ->constrained('sub_sub_categories')
                ->onDelete('set null')
                ->comment('Foreign key: Links to the sub_sub_category (nullable)');

            $table->foreignId('brand_id')  // Links to the brands table
                ->nullable()
                ->constrained('brands')
                ->onDelete('set null')
                ->comment('Foreign key: Links to the brand (nullable)');

            $table->string('sku')->unique()->comment('Stock Keeping Unit: Unique inventory code');


            // Core Product Information
            $table->string('name')->unique()->comment('Product name');
            $table->string('slug')->unique()->comment('SEO-friendly URL for the product');
            $table->text('short_description')->nullable()->comment('A short description of the product');
            $table->text('invoice_description')->nullable()->comment('A short description of the product for invoice, show after product name');
            $table->text('description')->nullable()->comment('Detailed description of the product');

            $table->decimal('base_price', 10, 2)->comment('Price of the product');
            $table->boolean('on_sale')->default(false)->comment('Indicates if the product is on sale');
            $table->decimal('discount_percentage', 5, 2)->nullable()->comment('Discount percentage applied to the product');
            $table->decimal('sale_price', 10, 2)->nullable()->comment('Sale price after discount');
            $table->dateTime('sale_start_date')->nullable()->comment('Start date for the product sale');
            $table->dateTime('sale_end_date')->nullable()->comment('End date for the product sale');

            // Product Attachments
            $table->boolean('is_downloadable')->default(false)->comment('Indicates if the product is downloadable');
            $table->string('download_link')->nullable()->comment('Download link for the product, if applicable');
            $table->boolean('is_backorderable')->default(false)->comment('Indicates if the product is backorderable');

            // SEO and Marketing
            $table->string('meta_title')->nullable()->comment('SEO title for the product');
            $table->string('meta_keywords')->nullable()->comment('SEO keywords for the product');
            $table->string('meta_description')->nullable()->comment('SEO description for the product');

            // Stock and Inventory
            $table->integer('stock_quantity')->default(0)->comment('Quantity of the product in stock');
            $table->boolean('is_featured')->default(false)->comment('Indicates if the product is featured');
            $table->boolean('is_new')->default(false)->comment('Indicates if the product is new');

            // Images and Media
            $table->string('feature_image_path')->nullable()->comment('Path to the main product image');

            // Product Variations (Size, Color, Material, etc.)
            $table->json('attributes')->nullable()->comment('Dynamic attributes like size, color, material, etc.');

            $table->decimal('weight', 5, 2)->nullable()->comment('Weight of the product in kilograms');
            $table->string('dimensions')->nullable()->comment('Dimensions of the product, e.g., "10x5x2 cm"');


            $table->json('additional_information')->nullable()->comment('Additional information about the product in JSON format');
            $table->boolean('is_in_stock')->default(true)->comment('Indicates if the product is in stock or out of stock');
            $table->integer('sold_quantity')->default(0)->comment('Quantity of the product sold');
            $table->json('additional_shipping_info')->nullable()->comment('Additional shipping information in JSON format');

            // Product Categories and Tags
            $table->json('tags')->nullable()->comment('Product tags in JSON format');

            //Additional Information
            $table->boolean('is_active')->default(true)->comment('Indicates if the product is active and available');

            // Timestamps
            $table->timestamps(); // Laravel will automatically create created_at and updated_at fields
        });
    }

    //$table->json('image_gallery')->nullable()->comment('Gallery of additional product images in JSON format');
    //$table->json('video_links')->nullable()->comment('Product video links in JSON format');
    //$table->string('shipping_weight')->nullable()->comment('Shipping weight of the product');
    //$table->string('shipping_dimensions')->nullable()->comment('Shipping dimensions of the product');
    //$table->boolean('is_digital')->default(false)->comment('Indicates if the product is digital or physical');
    //$table->string('file_path')->nullable()->comment('Path to the digital product file, if applicable');
    //$table->string('file_type')->nullable()->comment('File type of the digital product file, if applicable');
    //$table->json('related_products')->nullable()->comment('Related products in JSON format');

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
