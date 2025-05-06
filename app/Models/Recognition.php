<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recognition extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'recognition_date',
        'privacy_setup_id',
        'is_active',
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    public function documents()
    {
        return $this->hasMany(RecognitionFile::class, 'recognition_id');
    }
    public function images()
    {
        return $this->hasMany(RecognitionImage::class, 'recognition_id');
    }
}
