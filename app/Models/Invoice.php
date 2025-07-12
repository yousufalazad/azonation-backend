<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        // 'invoice_code',
        'billing_code',
        'order_code',
        'order_id',
        'user_id',
        'org_name',
        'description',
        'total_amount',
        'amount_paid',
        'balance_due',
        'currency_code',
        'generate_date',
        'issue_date',
        'due_date',
        'terms',
        'invoice_note',
        'is_published',
        'invoice_status',
        'payment_status',
        'admin_note',
        'is_active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function administrator()
    {
        return $this->belongsTo(OrgAdministrator::class, 'user_id', 'org_type_user_id');
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
            $model->invoice_code = 'I' . $randomString;
        });
    }
}
