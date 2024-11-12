<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DialingCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id', 
        'dialing_code',
        'is_active'
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
