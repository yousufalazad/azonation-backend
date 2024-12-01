<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingGuestAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'guest_name', 
        'about_guest', 
        'attendance_type_id', 
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
