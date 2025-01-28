<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoragePricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'storage_package_id',
        'price_rate',
        'note',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function package()
    {
        return $this->belongsTo(StoragePackage::class, 'storage_package_id', 'id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }
}
