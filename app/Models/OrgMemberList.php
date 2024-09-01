<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgMemberList extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
        'org_type_user_id',
        'individual_type_user_id',
        'existing_org_membership_id',
        'membership_type_id',
        'joining_date',
        'end_date',
        'status'
    ];

    public function individual()
    {
        return $this->belongsTo(User::class, 'individual_type_user_id', 'id');
    }

    public function connectedorg()
    {
        return $this->belongsTo(User::class, 'org_type_user_id', 'id');
    }
}