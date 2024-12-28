<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'image_path',
        'file_name',
        'mime_type',
        'file_size',
        'is_public',  // Whether the profile image is publicly visible or not
        'is_active'
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
