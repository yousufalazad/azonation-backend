<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'start_year', 
        'end_year', 
        'goals', 
        'activities', 
        'budget', 
        'start_date', 
        'end_date', 
        'privacy_setup_id', 
        'published', 
        'status'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
