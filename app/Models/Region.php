<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ManagementPricing;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'title',
        'is_active'
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    public function managementPricings()
{
    return $this->hasOne(ManagementPricing::class, 'region-id', 'id')->where('is_active', true);
}

public function regionCurrency()
{
    return $this->hasOne(RegionCurrency::class, 'region_id', 'id')->where('is_active', true);
}

}
