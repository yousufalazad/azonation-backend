<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Currency;

class RegionCurrency extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_id',
        'region_id',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id')->where('is_active', true);
    }
}
