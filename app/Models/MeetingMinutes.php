<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingMinutes extends Model
{
    use HasFactory;
    protected $fillable = [
        'meeting_id',
        'minutes', 
        'decisions', 
        'note', 
        'start_time', 
        'end_time', 
        'follow_up_tasks', 
        'tags', 
        'action_items', 
        'file_attachments',
        'video_link',
        'meeting_location',
        'confidentiality',
        'approval_status',
        'status',
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
