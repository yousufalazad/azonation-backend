<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_type_id',
        'category_id',
        'sub_category_id',
        'sub_sub_category_id',
        'brand_id',
        'sku',
        'name',
        'slug',
        'short_description',
        'invoice_description',
        'description',
        'base_price',
        'on_sale',
        'discount_percentage',
        'sale_price',
        'sale_start_date',
        'sale_end_date',
        'is_downloadable',
        'download_link',
        'is_gift_card',
        'is_refundable',
        'is_customizable',
        'is_backorderable',
        'is_sold_individually',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'stock_quantity',
        'is_featured',
        'is_new',
        'feature_image',
        'attributes',
        'weight',
        'dimensions',
        'additional_information',
        'is_in_stock',
        'sold_quantity',
        'additional_shipping_info',
        'shipping_rules',
        'tags',
        'warranty_period',
        'is_active'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
