<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgPhoneNumber extends Model
{
    use HasFactory;
    protected $fillable = [
        'org_id',
        'dialing_code_id',
        'phone_number',
        'phone_type',
        'status'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
