<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'short_name',
        'subject',
        'date',
        'time',
        'description',
        'address',
        'agenda',
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
