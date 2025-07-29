<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'short_description',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'venue_name',
        'venue_address',
        'requirements',
        'note',
        'status',
        'conduct_type'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function documents()
    {
        return $this->hasMany(ProjectFile::class, 'project_id');
    }
    public function images()
    {
        return $this->hasMany(ProjectImage::class, 'project_id');
    }

    public function projectAttendances()
    {
        return $this->hasMany(ProjectAttendance::class, 'project_id');
    }
}
