<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceRate extends Model
{
    use HasFactory;
    protected $fillable = [
        'package_id',
        'tier1',
        'tier2',
        'tier3',
        'tier4',
        'tier5',
        'tier6',
        'tier7',
        'tier8',
        'tier9',
        'tier10',
        'tier11',
        'tier12',
        'tier13',
        'tier14',
        'tier15',
        'tier16',
        'tier17',
        'tier18',
        'tier19',
        'tier20',        
        'status'  
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
