<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'language_name', 
        'language_code', 
        'default', 
        'is_active'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
