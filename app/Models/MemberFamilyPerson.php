<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A person in a member's family. Belongs to the member; organisations only see it when shared.
class MemberFamilyPerson extends Model
{
    protected $table = 'member_family_people';

    protected $fillable = ['user_id', 'name', 'relationship', 'birth_year', 'gender', 'note'];

    protected $casts = ['birth_year' => 'integer'];
}
