<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgMembershipStatusLog extends Model
{
    protected $table = 'org_membership_status_logs';

    protected $fillable = [
        'org_type_user_id',
        'individual_type_user_id',
        'membership_status_id',
        'membership_status_start',
        'membership_status_end',
        'membership_status_duration_days',
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
     * Individual User (Member)
     */
    public function individualUser()
    {
        return $this->belongsTo(User::class, 'individual_type_user_id');
    }

    /**
     * Previous Membership Status
     */
    public function membershipStatus()
    {
        return $this->belongsTo(MembershipStatus::class, 'membership_status_id');
    }
}
