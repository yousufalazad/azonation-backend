<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCurrency extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency_id',
        'status'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Currency
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
