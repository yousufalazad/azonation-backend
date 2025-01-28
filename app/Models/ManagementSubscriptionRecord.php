<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementSubscriptionRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'old_mgmt_pakg_id',
        'old_mgmt_pakg_name',
        'old_mgmt_price_rate',
        'old_mgmt_pakg_start_date',
        'old_mgmt_pakg_end_date',
        'new_mgmt_pakg_id',
        'new_mgmt_pakg_name',
        'new_mgmt_price_rate',
        'currency_code',
        'change_date',
        'change_reason',
        'is_active'
    ];

    
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
