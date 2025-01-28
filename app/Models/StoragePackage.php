<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoragePackage extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'storage_max_limit',
        'is_storage_grace_period_allow',
        'is_over_use_allow',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
