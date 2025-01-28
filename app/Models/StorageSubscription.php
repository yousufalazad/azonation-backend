<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'storage_package_id',
        'start_date',
        'subscription_status',
        'reason_for_action',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function package()
    {
        return $this->belongsTo(StoragePackage::class, 'storage_package_id', 'id');
    }
}
