<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'attendance_type_id',
        'time', 
        'note', 
        'is_active'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
