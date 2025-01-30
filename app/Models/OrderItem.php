<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_attributes',
        'unit_price',
        'quantity',
        'total_price',
        'discount_amount',
        'note',
        'is_active'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
