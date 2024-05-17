<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'azon_id',
        'title',
        'first_name',
        'last_name',
        'gender',
        'status',
    ];
}
