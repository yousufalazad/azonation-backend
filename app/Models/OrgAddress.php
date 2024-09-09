<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgAddress extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'address_line',
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
