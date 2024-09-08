<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Individual extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'azon_id',
        'full_name',
        'status',
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
