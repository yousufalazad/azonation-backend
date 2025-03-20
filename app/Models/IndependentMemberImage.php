<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndependentMemberImage extends Model
{
    protected $fillable = [
        'org_independent_member_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'is_public',
        'is_active',
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
