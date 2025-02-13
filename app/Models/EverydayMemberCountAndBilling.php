<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EverydayMemberCountAndBilling extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'day_total_member',
        'day_total_bill',
        'is_active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
