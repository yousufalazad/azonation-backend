<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'address_line_one',
        'address_line_two',
        'city',
        'state_or_region',
        'postal_code',
        'country_id'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
