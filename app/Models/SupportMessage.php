<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $fillable = ['support_request_id', 'user_id', 'is_staff', 'body'];

    protected $casts = ['is_staff' => 'boolean'];

    public function request()
    {
        return $this->belongsTo(SupportRequest::class, 'support_request_id');
    }
}
