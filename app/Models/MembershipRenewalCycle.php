<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipRenewalCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_in_months',
        'is_active'
    ];
}
