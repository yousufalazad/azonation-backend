<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'billing_code',
        'order_code',
        'order_date',
        'user_name',

        'sub_total',
        'discount_amount',
        'shipping_cost',
        'total_tax',
        'credit_applied',
        'total_amount',

        'discount_title',
        'tax_rate',
        'currency_code',
        'coupon_code',

        'payment_method',
        'billing_address',
        'attn_org_administrator',
        'billing_phone_number',
        'billing_email',

        'user_country',
        'user_region',
        'is_active'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
   
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'id');
    }
   

    public function orderDetail()
    {
        return $this->hasOne(OrderDetail::class, 'order_id', 'id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

   
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Define the allowed characters: uppercase letters A-Z and digits 0-9
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

            // Generate a 15-character random string from the allowed characters
            $randomString = '';
            for ($i = 0; $i < 14; $i++) {
                $randomString .= $characters[random_int(0, strlen($characters) - 1)];
            }

            // Prefix the random string with 'I' for the Invoice code
            $model->order_code = 'O' . $randomString;
        });
    }
}
