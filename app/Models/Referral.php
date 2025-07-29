<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_code_id',
        'referrer_id',
        'referred_user_id',
        'email',
        'ip_address',
        'user_agent',
        'signup_completed',
        'reward_given',
        'rewarded_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // The referral code used
    public function referralCode()
    {
        return $this->belongsTo(ReferralCode::class);
    }

    // The user who referred someone
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    // The user who was referred (after signup)
    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    // Reward record (optional)
    public function rewards()
    {
        return $this->hasMany(ReferralReward::class);
    }
}
