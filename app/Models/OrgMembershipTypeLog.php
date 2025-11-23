<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgMembershipTypeLog extends Model
{
    protected $table = 'org_membership_type_logs';

    protected $fillable = [
        'org_type_user_id',
        'individual_type_user_id',
        'membership_type_id',
        'membership_type_start',
        'membership_type_end',
        'membership_type_duration_days',
        'changed_at',
        'reason',
    ];

    /**
     * Organisation User
     */
    public function orgUser()
    {
        return $this->belongsTo(User::class, 'org_type_user_id');
    }

    /**
     * Individual Member User
     */
    public function individualUser()
    {
        return $this->belongsTo(User::class, 'individual_type_user_id');
    }

    /**
     * Previous Membership Type
     */
    public function membershipType()
    {
        return $this->belongsTo(MembershipType::class, 'membership_type_id');
    }
}
