<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingMinutes extends Model
{
    use HasFactory;
    protected $fillable = [
        'meeting_id',
        'prepared_by',
        'reviewed_by',
        'minutes',
        'decisions',
        'note',
        'file_attachments',
        'start_time',
        'end_time',
        'follow_up_tasks',
        'tags',
        'action_items',
        'meeting_location',
        'video_link',
        'privacy_setup_id',
        'approval_status',
        'is_publish',
        'is_active',
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
