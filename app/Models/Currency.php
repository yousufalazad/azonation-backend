<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;
    protected $fillable = [
        'currency_name',
        'currency_code',
        'currency_symbol',
        'unit_name',
        'is_active',
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    public function regionCurrency()
    {
        return $this->belongsTo(RegionCurrency::class, 'currency_id', 'id')->where('is_active', true);
    }
}
