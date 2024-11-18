<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // app/Models/User.php

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
    
    public function country()
    {
        return $this->hasOne(UserCountry::class);
    }
    
    public function currency()
    {
        return $this->hasOne(UserCurrency::class);
    }
    

    protected $fillable = [
        'azon_id',
        'name',
        'type',
        'username',
        'email',
        'password',
        'image',
        'verification_token',
        'email_verified_at',
        'remember_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
