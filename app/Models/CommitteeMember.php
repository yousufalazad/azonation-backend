<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    use HasFactory;
    protected $fillable = [
        'committee_id',
        'user_id',
        'designation_id',
        'start_date',
        'end_date',
        'note',
        'is_active'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
    
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }
}
