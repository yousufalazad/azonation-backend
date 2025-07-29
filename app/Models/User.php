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
use App\Models\ProfileImage;
use App\Models\OrgAdministrator;
use App\Models\Address;
use App\Models\PhoneNumber;
use App\Models\UserCountry;
use App\Models\Country;
use App\Models\CountryRegion;
use App\Models\Region;
use App\Models\ManagementSubscription;
use App\Models\StorageSubscription;
use App\Models\AccountsFund;
use App\Models\ManagementPackage;
use App\Models\ManagementAndStorageBilling;
use App\Models\RegionCurrency;
use App\Models\Currency;
use App\Models\ReferralCode;
use App\Models\Referral;
use App\Models\ReferralReward;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'azon_id',
        'first_name',
        'last_name',
        'org_name',
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

    public function orgProfileImage(): HasOne
    {
        return $this->hasOne(ProfileImage::class, 'id', 'org_type_user_id');
    }


    public function orgAdministrator()
    {
        return $this->hasOne(OrgAdministrator::class, 'org_type_user_id', 'id')->where('is_primary', 1);
    }

    public function address()
    {
        return $this->hasOne(Address::class, 'user_id', 'id');
    }

    public function phoneNumber()
    {
        return $this->hasOne(PhoneNumber::class, 'user_id', 'id');
    }


    public function userCountry()
    {
        return $this->hasOne(UserCountry::class, 'user_id', 'id')->where('is_active', true);
    }

    public function country()
    {
        return $this->hasOne(Country::class, 'id', 'user_id');
    }

    public function countryRegion()
    {
        return $this->hasOne(CountryRegion::class, 'country_id', 'region_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    //for user subscription package
    public function managementSubscription()
    {
        return $this->hasOne(ManagementSubscription::class, 'user_id', 'id')->where('is_active', true);
    }
    public function storageSubscription()
    {
        return $this->hasOne(StorageSubscription::class, 'user_id', 'id')->where('is_active', true);
    }

    public function accountFund()
    {
        return $this->hasMany(AccountsFund::class, 'user_id', 'id')->where('is_active', true);
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

    // User's own referral code
    public function referralCode()
    {
        return $this->hasOne(ReferralCode::class);
    }

    // Referrals this user made (as referrer)
    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    // Referrals where this user was referred
    public function referredBy()
    {
        return $this->hasOne(Referral::class, 'referred_user_id');
    }

    // Rewards received from referrals
    public function referralRewards()
    {
        return $this->hasMany(ReferralReward::class);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            if (! $user->referralCode) {
                \App\Models\ReferralCode::create([
                    'user_id' => $user->id,
                    'code' => strtoupper('AZN' . str_pad($user->id, 6, '0', STR_PAD_LEFT)),
                    'reward_type' => 'credit',
                    'reward_value' => 10, // e.g. 10 points or dollars
                    'status' => 'active',
                ]);
            }
        });
    }



    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reset_code', 'reset_code_expires_at']);
        });
    }
}
