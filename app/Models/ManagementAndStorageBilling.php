<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementAndStorageBilling extends Model
{
    use HasFactory;
    protected $fillable = [
        //'billing_code',
        'user_id',
        'org_name',
        'service_month',
        'service_year',
        'billing_month',
        'billing_year',
        'period_start',
        'period_end',
        'total_member',
        'total_management_bill_amount',
        'total_storage_bill_amount',
        'currency_code',
        'bill_status',
        'admin_note',
        'is_active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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

            // Prefix the random string with 'T' for the final transaction ID
            $model->billing_code = 'B' . $randomString;
        });
    }
}
