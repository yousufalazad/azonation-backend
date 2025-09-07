<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgMembershipRenewalPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_type_user_id',
        'org_membership_type_id',
        'org_mem_renewal_cycle_id',
        'member_renewal_cycle_id',
        'currency',
        'unit_amount_minor',
        'is_recurring',
        'valid_from',
        'valid_to',
        'org_notes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function orgMembershipType()
    {
        return $this->belongsTo(OrgMembershipType::class);
    }

    public function orgMembershipRenewalCycle()
    {
        return $this->belongsTo(OrgMembershipRenewalCycle::class, 'org_mem_renewal_cycle_id');
    }

    public function memberRenewalCycle()
    {
        return $this->belongsTo(MembershipRenewalCycle::class, 'member_renewal_cycle_id');
    }
}