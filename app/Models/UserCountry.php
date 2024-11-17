<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCountry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'country_id', 
        'is_active'
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
