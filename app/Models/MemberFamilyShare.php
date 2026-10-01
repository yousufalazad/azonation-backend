<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// What one organisation may see of a member's family: "numbers" or "details". No row = nothing.
class MemberFamilyShare extends Model
{
    protected $fillable = ['user_id', 'org_id', 'level'];
}
