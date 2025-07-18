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
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    
    public function documents()
    {
        return $this->hasMany(StrategicPlanFile::class, 'strategic_plan_id');
    }
    public function images()
    {
        return $this->hasMany(StrategicPlanImage::class, 'strategic_plan_id');
    }
}
