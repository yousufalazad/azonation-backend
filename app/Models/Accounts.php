<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    protected $fillable = [
        'transaction_code',
        'user_id',
        'transaction_title',
        'description',
        'accounts_fund_id',
        'date',
        'type',
        'amount',
        'is_active'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function funds()
    {
        return $this->belongsTo(AccountsFund::class, 'accounts_fund_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(AccountsTransactionFile::class, 'accounts_id');
    }
    public function images()
    {
        return $this->hasMany(AccountsTransactionImage::class, 'accounts_id');
    }

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
            for ($i = 0; $i < 14; $i++) {
                $randomString .= $characters[random_int(0, strlen($characters) - 1)];
            }

            // Prefix the random string with 'T' for the final transaction ID
            $model->transaction_code = 'T' . $randomString;
        });
    }
}
