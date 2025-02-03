<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'history',
        'is_active',
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

    public function documents()
    {
        return $this->hasMany(HistoryFile::class, 'history_id');
    }
    public function images()
    {
        return $this->hasMany(HistoryImage::class, 'history_id');
    }
}
