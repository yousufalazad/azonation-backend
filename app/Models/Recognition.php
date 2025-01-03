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
        'status',
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
