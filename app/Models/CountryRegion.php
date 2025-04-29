<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RegionCurrency;

class CountryRegion extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'region_id',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

   //for management and storage everyday bill calculation
    public function region()
    {
        return $this->hasOne(Region::class, 'id', 'region_id');
    }

    public function regionCurrency ()
    {
        return $this->hasOne(RegionCurrency::class, 'region_id', 'currency_id')->where('is_active', true);
    }
    
}
