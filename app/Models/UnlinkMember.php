<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnlinkMember extends Model
{
     protected $fillable = [
        'user_id',
        'existing_membership_id',
        'membership_type_id',
        'membership_start_date',
        'membership_status_id',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'address',
        'note',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function membershipType(): BelongsTo
    {
        return $this->belongsTo(MembershipType::class, 'membership_type_id', 'id');
    }
    public function membershipStatus(): BelongsTo
    {
        return $this->belongsTo(MembershipStatus::class, 'membership_status_id', 'id');
    }
    public function image()
    {
        return $this->hasOne(UnlinkMemberImage::class, 'unlink_member_id');
    }
}
