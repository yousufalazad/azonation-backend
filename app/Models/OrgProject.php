<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgProject extends Model
{
    use HasFactory;
    protected $fillable = [
        'org_id',
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

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
