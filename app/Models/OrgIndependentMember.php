<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgIndependentMember extends Model
{
    use HasFactory;

    protected $table = 'org_independent_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'mobile',
        'address',
        'note',
        'is_active',
        'image_path',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
