<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'name',
        'short_description',
        'description',
        'date',
        'time',
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
        return $this->hasMany(EventFile::class, 'event_id');
    }
    public function images()
    {
        return $this->hasMany(EventImage::class, 'event_id');
    }

    public function eventAttendances()
    {
        return $this->hasMany(EventAttendance::class, 'event_id');
    }
}
