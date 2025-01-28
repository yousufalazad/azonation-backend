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

    public function regionCurrency()
    {
        return $this->belongsTo(RegionCurrency::class, 'region_id', 'region_id');
    }

    //     public function region()
    // {
    //     return $this->belongsTo(Region::class);
    // }
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }
}
