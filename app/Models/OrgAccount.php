<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'user_id',
        'title',
        'account_fund_id',
        'transaction_date',
        'transaction_type',
        'transaction_amount',
        'balance',
        'description'
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Define the allowed characters: uppercase letters A-Z and digits 0-9
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

            // Generate a 10-character random string from the allowed characters
            $randomString = '';
            for ($i = 0; $i < 10; $i++) {
                $randomString .= $characters[random_int(0, strlen($characters) - 1)];
            }

            // Prefix the random string with 'T' for the final transaction ID
            $model->transaction_id = 'T' . $randomString;
        });
    }
}
