<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperAdmin extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'azon_id',
        'admin_name',
        'short_description',
        'note',
        'supervision',
        'start_date',
        'end_date',
        'status',
    ];
}
