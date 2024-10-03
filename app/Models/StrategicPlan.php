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
    
    // If you have a relationship to the user model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
