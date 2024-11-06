<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceRate extends Model
{
    use HasFactory;
    protected $fillable = [
        'package_id',
        'region1',
        'region2',
        'region3',
        'region4',
        'region5',
        'region6',
        'region7',
        'region8',
        'region9',
        'region10',
        'region11',
        'region12',
        'region13',
        'region14',
        'region15',
        'region16',
        'region17',
        'region18',
        'region19',
        'region20',        
        'status'  
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
