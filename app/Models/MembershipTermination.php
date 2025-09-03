<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipTermination extends Model
{
    protected $table = 'membership_terminations';

    protected $fillable = [
        'org_type_user_id',
        'individual_type_user_id',
        'terminated_member_name',
        'terminated_member_email',
        'terminated_member_mobile',
        'terminated_at',
        'processed_at',
        'membership_termination_reason_id',
        'org_administrator_id',
        'rejoin_eligible',
        'file_path',
        'membership_duration_days',
        'membership_status_before_termination',
        'membership_type_before_termination',
        'joined_at',
        'org_note',
    ];


    protected $casts = [
        'terminated_at' => 'datetime',
        'processed_at' => 'datetime',
        'rejoin_eligible' => 'boolean',
        'membership_duration_days' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
