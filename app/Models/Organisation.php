<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'azon_id',
        'org_name',
        'short_description',
        'status',
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
