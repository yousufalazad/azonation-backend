<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Founder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'founder_user_id', // individual type user_id
        'name',
        'designation'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Relationship with the user who is a founder.
     */
    public function founders()
    {
        return $this->belongsTo(User::class, 'founder_user_id');
    }

    public function user_image()
    {
        return $this->hasOne(ProfileImage::class, 'user_id', 'founder_user_id');
    }

    public function founder_image()
    {
        return $this->hasOne(FounderProfileImage::class, 'founder_id');
    }
    public function getImageAttribute()
    {
        return $this->user_image ?: $this->founder_image;
    }
    
    // public function image()
    // {
    //     return $this->founder_image;

    //     // if ($this->user_image()->exists()) {
    //     //     return $this->user_image()->first();
    //     // }

    //     // if ($this->founder_image()->exists()) {
    //     //     return $this->founder_image()->first();
    //     // }

    //     // return null;
    // }
}
