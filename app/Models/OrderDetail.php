<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'shipping_address',
        'shipping_status',
        'shipping_method',
        'shipping_note',
        'customer_note',
        'admin_note',
        'tracking_number',
        'delivery_date_expected',
        'delivery_date_actual',
        'order_status',
        'cancelled_at',
        'cancellation_reason'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
