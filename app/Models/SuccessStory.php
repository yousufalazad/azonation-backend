<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuccessStory extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'history', 'status', 'user_id', 'image'];

    // Relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
