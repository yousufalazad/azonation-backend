<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DialingCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'country_name',    
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
