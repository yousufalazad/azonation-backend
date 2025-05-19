<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


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

    public function regionCurrency()
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

    // app/Models/User.php or your specific model


    public static function generateUniqueAzonId()
    {
        do {
            $azonId = str_pad(mt_rand(0, 9999999999999), 13, '0', STR_PAD_LEFT);
        } while (self::where('azon_id', $azonId)->exists());

        return $azonId;
    }

    // public static function generateUniqueUsername($name = null)
    // {
    //     // Converts name to lowercase and removes all non-alphabetic characters
    //     $base = $name ? preg_replace('/[^a-z]/', '', strtolower($name)) : 'user';

    //     do {
    //         // Generates a 3-letter random alphabet-only suffix
    //         $suffix = self::randomLetters();
    //         $username = $base . $suffix;
    //     } while (self::where('username', $username)->exists()); // Ensures uniqueness

    //     return $username;
    // }

    // public static function randomLetters($length = 3)
    // {
    //     //Generates random alphabetic characters
    //     return substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz', $length)), 0, $length);
    // }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            //Generates unique 13-digit numeric azon_id
            $model->azon_id = self::generateUniqueAzonId();

            //Generates unique, alphabet-only username based on name
            // $model->username = self::generateUniqueUsername($model->name);
        });
    }
}
