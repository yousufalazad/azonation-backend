<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSummaryImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'event_summary_id', 
        'file_path', 
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
