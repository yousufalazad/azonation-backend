<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
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

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(YearPlanFile::class, 'year_plan_id');
    }
    public function images()
    {
        return $this->hasMany(YearPlanImage::class, 'year_plan_id');
    }
}
