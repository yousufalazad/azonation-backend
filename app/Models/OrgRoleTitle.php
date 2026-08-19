<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgRoleTitle extends Model
{
    use HasFactory;

    protected $table = 'org_role_titles';

    protected $fillable = [
        'org_type_user_id',
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /* ================= RELATIONS ================= */

    public function organisation()
    {
        return $this->belongsTo(User::class, 'org_type_user_id');
    }

    public function members()
    {
        return $this->hasMany(OrgMemberRoleTitle::class, 'org_role_title_id');
    }
}