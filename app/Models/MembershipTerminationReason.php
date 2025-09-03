<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class MembershipTerminationReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
        'description',
        'is_active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
