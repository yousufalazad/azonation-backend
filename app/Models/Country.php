<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_code_alpha_3',
        'iso_code_alpha_2',
        'numeric_code',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
