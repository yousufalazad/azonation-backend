<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        //'invoice_code',
        'user_id',
        'user_name',
        'billing_address',
        'billing_id',
        'billing_code',
        'item_name',
        'item_description',
        'generated_at',
        'issued_at',
        'due_at',
        'total_active_member',
        'total_honorary_member',
        'total_billable_active_member',
        'subscribed_package_name',
        'price_rate',
        'currency',
        'subtotal',
        'discount_title',
        'discount',
        'tax',
        'balance',
        'invoice_note',
        'invoice_status',
        'credit_applied',
        'is_published',
        'payment_status',
        'payment_status_reason',
        'admin_note'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];


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
            $model->invoice_code = 'I' . $randomString;
        });
    }
}
