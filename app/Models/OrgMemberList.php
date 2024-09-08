<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//Represents the membership of individuals in an organization.
class OrgMemberList extends Model
{
    use HasFactory;
    protected $fillable = [
        'org_type_user_id',
        'individual_type_user_id',
        'existing_org_membership_id',
        'membership_type_id',
        'joining_date',
        'end_date',
        'status'
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    //Get the organization user related to this membership.
    public function individual(): BelongsTo
    {
        return $this->belongsTo(User::class, 'individual_type_user_id', 'id');
    }

    public function connectedorg(): BelongsTo
    {
        return $this->belongsTo(User::class, 'org_type_user_id', 'id');
    }
}