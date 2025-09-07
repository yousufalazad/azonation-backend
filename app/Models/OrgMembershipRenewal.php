<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgMembershipRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_type_user_id',
        'individual_type_user_id',
        'membership_renewal_cycle_id',
        'period_start',
        'period_end',
        'amount_paid',
        'status',
        'initiated_by',
        'initiated_source',
        'attempt_count',
        'last_attempt_at',
        'renewed_at',
        'invoice_id',
        'payment_id',
        'failure_code',
        'failure_message',
        'org_notes',
        'idempotency_key',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'last_attempt_at' => 'datetime',
        'renewed_at' => 'datetime',
    ];
}