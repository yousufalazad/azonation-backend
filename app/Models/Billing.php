<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;
    protected $fillable = [
        'billing_code',
        'user_id',
        'user_name',
        'description',
        'billing_address',
        'item_name',
        'period_start',
        'period_end',
        'service_month',
        'billing_month',
        'total_active_member',
        'total_billable_active_member',
        'price_rate',
        'bill_amount',
        'status',
        'admin_notes',
        'is_active',
    ];
    

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
