<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgMemberRoleTitle extends Model
{
    use HasFactory;

    protected $table = 'org_member_role_titles';

    protected $fillable = [
        'org_type_user_id',
        'individual_type_user_id',
        'org_role_title_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    /* ================= RELATIONS ================= */

    // Organisation
    public function organisation()
    {
        return $this->belongsTo(User::class, 'org_type_user_id');
    }

    // Individual Member
    public function member()
    {
        return $this->belongsTo(User::class, 'individual_type_user_id');
    }

    // Role Title
    public function roleTitle()
    {
        return $this->belongsTo(OrgRoleTitle::class, 'org_role_title_id');
    }
}
