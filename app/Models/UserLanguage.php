<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLanguage extends Model
{
     protected $fillable = [
        'user_id',
        'language_id',
        'is_active'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
    public function userLanguageName()
    {
        return $this->hasOne(Language::class, 'id', 'language_id')
            ->select('id', 'language_name');
    }

}
