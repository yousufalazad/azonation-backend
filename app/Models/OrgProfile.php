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
        'status',
    ];
}
