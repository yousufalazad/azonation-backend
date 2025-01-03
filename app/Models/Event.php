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
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
