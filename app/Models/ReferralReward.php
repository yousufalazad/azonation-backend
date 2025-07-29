<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'user_id',
        'reward_type',
        'amount',
        'status',
        'rewarded_at',
        'notes',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // Referral that triggered this reward
    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }

    // User who received the reward
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
