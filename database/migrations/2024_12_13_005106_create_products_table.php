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

            $table->string('currency', 3)->default('USD')->comment('Currency code for the price, e.g., "USD"');
            $table->decimal('base_price', 10, 2)->comment('Price of the product');
            $table->decimal('original_base_price', 10, 2)->nullable()->comment('Original price before discounts');
            $table->boolean('on_sale')->default(false)->comment('Indicates if the product is on sale');
            $table->decimal('discount_percentage', 5, 2)->nullable()->comment('Discount percentage applied to the product');
            $table->decimal('sale_price', 10, 2)->nullable()->comment('Sale price after discount');
            $table->dateTime('sale_start_date')->nullable()->comment('Start date for the product sale');
            $table->dateTime('sale_end_date')->nullable()->comment('End date for the product sale');
            $table->boolean('is_taxable')->default(true)->comment('Indicates if the product is taxable');
            //$table->decimal('tax_rate', 5, 2)->nullable()->comment('Tax rate applied to the product');
            //$table->decimal('shipping_cost', 10, 2)->nullable()->comment('Shipping cost for the product');
            //$table->decimal('weight', 5, 2)->nullable()->comment('Weight of the product in kilograms');
            //$table->string('dimensions')->nullable()->comment('Dimensions of the product, e.g., "10x5x2 cm"');
            $table->boolean('is_downloadable')->default(false)->comment('Indicates if the product is downloadable');
            $table->string('download_link')->nullable()->comment('Download link for the product, if applicable');
            $table->boolean('is_virtual')->default(false)->comment('Indicates if the product is virtual or physical');
            $table->boolean('is_gift_card')->default(false)->comment('Indicates if the product is a gift card');
            $table->boolean('is_refundable')->default(false)->comment('Indicates if the product is refundable');
            $table->boolean('is_customizable')->default(false)->comment('Indicates if the product is customizable');
            $table->boolean('is_backorderable')->default(false)->comment('Indicates if the product is backorderable');
            $table->boolean('is_sold_individually')->default(false)->comment('Indicates if the product can be sold individually');
            $table->boolean('is_downloadable_only')->default(false)->comment('Indicates if the product is downloadable only');

 

            // SEO and Marketing
            $table->string('meta_title')->nullable()->comment('SEO title for the product');
            $table->string('meta_keywords')->nullable()->comment('SEO keywords for the product');
            $table->string('meta_description')->nullable()->comment('SEO description for the product');

            // Stock and Inventory
            $table->integer('stock_quantity')->default(0)->comment('Quantity of the product in stock');
            $table->boolean('is_active')->default(true)->comment('Indicates if the product is active and available');
            $table->boolean('is_featured')->default(false)->comment('Indicates if the product is featured');
            $table->boolean('is_new')->default(false)->comment('Indicates if the product is new');

            // Images and Media
            $table->string('main_image')->nullable()->comment('Path to the main product image');
            $table->json('image_gallery')->nullable()->comment('Gallery of additional product images in JSON format');
            $table->json('video_links')->nullable()->comment('Product video links in JSON format');

            // Product Variations (Size, Color, Material, etc.)
            $table->json('attributes')->nullable()->comment('Dynamic attributes like size, color, material, etc.');

            $table->decimal('weight', 5, 2)->nullable()->comment('Weight of the product in kilograms');
            $table->string('dimensions')->nullable()->comment('Dimensions of the product, e.g., "10x5x2 cm"');
            $table->boolean('is_digital')->default(false)->comment('Indicates if the product is digital or physical');
            $table->string('file_path')->nullable()->comment('Path to the digital product file, if applicable');
            $table->string('file_type')->nullable()->comment('File type of the digital product file, if applicable');
            $table->json('additional_information')->nullable()->comment('Additional information about the product in JSON format');
            $table->boolean('is_in_stock')->default(true)->comment('Indicates if the product is in stock or out of stock');
            $table->integer('sold_quantity')->default(0)->comment('Quantity of the product sold');
            $table->json('additional_shipping_info')->nullable()->comment('Additional shipping information in JSON format');
            $table->json('custom_fields')->nullable()->comment('Custom fields for the product in JSON format');
            $table->json('shipping_rules')->nullable()->comment('Shipping rules for the product in JSON format');
            //$table->json('custom_options')->nullable()->comment('Custom options for the product in JSON format');
            //$table->json('product_options')->nullable()->comment('Product options in JSON format');
            //$table->json('product_variations')->nullable()->comment('Product variations in JSON format');
            $table->json('related_products')->nullable()->comment('Related products in JSON format');

            // Product Reviews and Ratings

            // Product Taxes and Shipping Rates
            // $table->decimal('shipping_rate', 10, 2)->nullable()->comment('Shipping rate for the product');
            // $table->json('tax_rates')->nullable()->comment('Tax rates for different regions in JSON format');
            // $table->json('shipping_rates')->nullable()->comment('Shipping rates for different regions in JSON format');
            // $table->json('product_reviews')->nullable()->comment('Product reviews in JSON format');
            // $table->json('product_ratings')->nullable()->comment('Product ratings in JSON format');

            // Product Categories and Tags
            //$table->json('categories')->nullable()->comment('Product categories in JSON format');
            $table->json('tags')->nullable()->comment('Product tags in JSON format');

            // Product SEO and Social Media

            // Shipping and Additional Information
            $table->string('shipping_weight')->nullable()->comment('Shipping weight of the product');
            $table->string('shipping_dimensions')->nullable()->comment('Shipping dimensions of the product');
            $table->enum('shipping_type', ['standard', 'express', 'free'])->nullable()->comment('Shipping type for the product');
            $table->integer('warranty_period')->nullable()->comment('Warranty period in months');

            // Timestamps
            $table->timestamps(); // Laravel will automatically create created_at and updated_at fields
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
