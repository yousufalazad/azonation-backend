<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrategicPlan extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'plan',
        'start_date',
        'end_date',
        'status',
        'image'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    
    // If you have a relationship to the user model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(StrategicPlanFile::class, 'strategic_plan_id');
    }
    public function images()
    {
        return $this->hasMany(StrategicPlanImage::class, 'strategic_plan_id');
    }
}
