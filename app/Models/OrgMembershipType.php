<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgMembershipType extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_type_user_id',
        'membership_type_id',
        'starts_on',
        'ends_on',
        'is_active',
        'is_public',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'meta' => 'array',
    ];

    public function membershipType()
    {
        return $this->belongsTo(MembershipType::class, 'membership_type_id');
    }
}
