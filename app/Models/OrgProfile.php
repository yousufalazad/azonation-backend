<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'short_description',
        'detail_description',
        'who_we_are',
        'what_we_do',
        'how_we_do',
        'mission',
        'vision',
        'value',
        'areas_of_focus',
        'causes',
        'impact',
        'why_join_us',
        'scope_of_work',
        'organising_date',
        'foundation_date',
        'status'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
