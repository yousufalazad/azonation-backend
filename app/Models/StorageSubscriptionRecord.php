<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageSubscriptionRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'old_storage_package_id',
        'old_storage_package_name',
        'old_storage_price_rate',
        'old_storage_package_start_date',
        'old_storage_package_end_date',
        'new_storage_package_id',
        'new_storage_package_name',
        'new_storage_price_rate',
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
