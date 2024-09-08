<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'individual_id',
        'image',
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
