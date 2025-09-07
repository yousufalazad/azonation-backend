<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgMembershipRenewalCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_type_user_id',
        'member_renewal_cycle_id',
        'alignment',
        'anchor_month',
        'anchor_day',
        'anchor_weekday',
        'use_last_day_of_month',
        'timezone',
        'proration_policy',
        'grace_days',
        'is_active',
    ];

    // Relationships (if applicable)
    public function orgTypeUser()
    {
        return $this->belongsTo(User::class, 'org_type_user_id');
    }

    public function memberRenewalCycle()
    {
        return $this->belongsTo(MembershipRenewalCycle::class, 'member_renewal_cycle_id');
    }
}
