<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionalTaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_rate', 
        'region_id',
        'is_active'
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
