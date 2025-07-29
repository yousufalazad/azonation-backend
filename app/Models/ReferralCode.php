<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'reward_type',
        'reward_value',
        'max_uses',
        'times_used',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    
    // The user who owns this referral code
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Referrals made using this code
    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }
}
