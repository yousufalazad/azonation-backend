<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgMemberList extends Model
{
    use HasFactory;
    protected $fillable = [
        'org_id',
        'individual_id',
        'existing_org_membership_id',
        'membership_type',
        'joining_date',
        'end_date',
        'status',
    ];

    public function individual()
    {
        return $this->belongsTo(Individual::class, 'individual_id', 'id');
    }

    public function connectedorg()
    {
        return $this->belongsTo(Organisation::class, 'org_id', 'id');
    }
}