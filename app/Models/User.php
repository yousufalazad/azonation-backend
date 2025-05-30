<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\HasOne;

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


    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function individualProfileImage(): HasOne
    {
        return $this->hasOne(ProfileImage::class, 'user_id', 'id');
    }

    //for user price rate
    public function userCountry()
    {
        return $this->hasOne(UserCountry::class, 'user_id', 'id');
    }
    //for user price rate
    public function country()
    {
        return $this->hasOne(Country::class, 'country_id', 'id');
    }

    //for user price rate
    public function countryRegion()
    {
        return $this->hasOne(CountryRegion::class, 'country_id', 'region_id');
    }

    //for user price rate
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    //for user subscription package
    public function managementSubscription()
    {
        return $this->hasOne(ManagementSubscription::class, 'user_id', 'id')->where('is_active', true);
    }

    //for user subscription package
    public function managementPackage()
    {
        return $this->hasOneThrough(
            ManagementPackage::class,
            ManagementSubscription::class,
            'user_id', // Foreign key on ManagementSubscription table...
            'id', // Foreign key on ManagementPackage table...
            'id', // Local key on User table...
            'management_package_id' // Local key on ManagementSubscription table...
        )->where('is_active', true);
    }

    public function storageSubscription()
    {
        return $this->hasOne(StorageSubscription::class, 'user_id', 'id')->where('is_active', true);
    }

    public function managementAndStorageBilling()
    {
        return $this->hasMany(ManagementAndStorageBilling::class, 'user_id', 'id')->where('is_active', true);
    }

    public function regionCurrency ()
    {
        return $this->hasOne(RegionCurrency::class, 'region_id', 'region_id')->where('is_active', true);
    }

    public function currency()
    {
        return $this->hasOne(Currency::class, 'currency_id', 'id')->where('is_active', true);
    }

    

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reset_code', 'reset_code_expires_at']);
        });
    }
}
