<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'history',
        'status',
        'image',
        'document'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    // If you have a relationship to the user model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
