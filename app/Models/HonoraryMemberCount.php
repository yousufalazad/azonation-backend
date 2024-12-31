<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HonoraryMemberCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'active_honorary_member',
        'is_billable'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
