<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'order_number',
        'total_amount',
        'discount_amount',
        'shipping_cost',
        'total_tax',
        'currency',
        'shipping_status',
        'shipping_address',
        'billing_address',
        'coupon_code',
        'shipping_method',
        'shipping_note',
        'customer_note',
        'admin_note',
        'tracking_number',
        'order_date',
        'delivery_date_expected',
        'delivery_date_actual',
        'status',
        'cancelled_at',
        'is_active'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
