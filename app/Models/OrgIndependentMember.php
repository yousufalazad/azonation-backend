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
        'first_name',
        'last_name',
        'email',
        'mobile',
        'address',
        'note',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function image()
    {
        return $this->hasOne(IndependentMemberImage::class, 'org_independent_member_id');
    }
}
