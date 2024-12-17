<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessType extends Model
{
    use HasFactory;
    protected $table = 'business_types';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'slug',
        'meta_description',
        'order',
        'is_active',
        'business_type_image_path',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
