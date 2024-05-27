<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgMemberList extends Model
{
    use HasFactory;
    protected $fillable = [
        'org_id',
        'individual_id',
        'joining_date',
        'end_date',
        'status',
    ];
}