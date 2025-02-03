<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserCountry;
use App\Models\ManagementSubscription;
use App\Models\ManagementAndStorageBilling;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'azon_id',
        'name',
        'type',
        'shortname',
        'username',
        'email',
        'password',
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

    public function userCountry()
    {
        return $this->hasOne(UserCountry::class);
    }


    public function managementSubscription()
    {
        return $this->hasOne(ManagementSubscription::class, 'user_id', 'id')->where('is_active', true);
    }

    public function country()
    {
        return $this->belongsTo(UserCountry::class, 'id', 'user_id')->where('is_active', true);
    }

    public function storageSubscription()
    {
        return $this->hasOne(StorageSubscription::class, 'user_id', 'id')->where('is_active', true);
    }
    public function managementAndStorageBilling()
    {
        return $this->hasMany(ManagementAndStorageBilling::class, 'user_id', 'id')->where('is_active', true);
    }
}
