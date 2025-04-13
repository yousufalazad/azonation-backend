<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_type_user_id',
        'individual_type_user_id',
        'existing_membership_id',
        'membership_type_id',
        'membership_start_date',
        'sponsored_user_id ',
        'is_active'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function individual(): BelongsTo
    {
        return $this->belongsTo(User::class, 'individual_type_user_id', 'id');
    }

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class, 'membership_type_id', 'id');
    }

    public function memberProfileImage(): BelongsTo
    {
        return $this->belongsTo(ProfileImage::class, 'individual_type_user_id', 'user_id');
    }

    public function connectedorg(): BelongsTo
    {
        return $this->belongsTo(User::class, 'org_type_user_id', 'id');
    }
}
