<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;
    protected $fillable = [
        //'billing_code',
        'user_id',
        'user_name',
        'description',
        'billing_address',
        'item_name',
        'period_start',
        'period_end',
        'service_month',
        'billing_month',
        'service_year',
        'billing_year',
        'total_active_member',
        'total_active_honorary_member',
        'total_billable_active_member',
        'subscribed_package_name',
        'price_rate',
        'currency',
        'bill_amount',
        'status',
        'admin_notes',
        'is_active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

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

            // Prefix the random string with 'T' for the final transaction ID
            $model->billing_code = 'B' . $randomString;
        });
    }
}
