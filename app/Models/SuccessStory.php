<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuccessStory extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'story', 'status', 'user_id'];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    // Relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(SuccessStoryFile::class, 'success_story_id');
    }
    public function images()
    {
        return $this->hasMany(SuccessStoryImage::class, 'success_story_id');
    }
}
