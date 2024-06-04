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
        'status',
    ];

    public function individual()
    {
        return $this->belongsTo(Individual::class, 'individual_id', 'id');
    }
}