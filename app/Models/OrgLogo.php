<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgLogo extends Model
{
    use HasFactory;
    protected $fillable = [
        'org_id',
        'image',
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
