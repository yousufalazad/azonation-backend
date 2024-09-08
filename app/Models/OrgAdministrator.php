<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgAdministrator extends Model
{
    use HasFactory;
    protected $fillable = [
        'org_id',
        'individual_id',
        'from_date',
        'end_date',
        'status'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    public function individual()
    {
        return $this->belongsTo(Individual::class, 'individual_id', 'id');
    }
}
