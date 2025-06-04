<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingImage extends Model
{
     protected $fillable = [
        'meeting_id',
        'image_path',
        'file_name',
        'mime_type',
        'file_size',
        'is_public',
        'is_active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
