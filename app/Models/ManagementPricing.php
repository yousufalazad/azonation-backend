<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'management_package_id',
        'price_rate',
        'note',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function managementPackage()
    {
        return $this->belongsTo(ManagementPackage::class, 'management_package_id', 'id')->where('is_active', true);
    }
}
