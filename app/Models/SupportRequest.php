<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportRequest extends Model
{
    public const CATEGORIES = ['billing', 'account', 'problem', 'idea', 'other'];

    protected $fillable = ['user_id', 'org_id', 'name', 'email', 'category', 'subject', 'status', 'source', 'last_activity_at'];

    protected $casts = ['last_activity_at' => 'datetime'];

    public function messages()
    {
        return $this->hasMany(SupportMessage::class)->orderBy('id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
