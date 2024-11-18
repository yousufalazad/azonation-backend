<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'meeting_id',
        'user_id', 
        'meeting_attendance_type_id', 
        'date', 
        'time', 
        'note', 
        'is_active'
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
