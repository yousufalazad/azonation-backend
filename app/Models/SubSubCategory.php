<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubSubCategory extends Model
{
    use HasFactory;

    protected $table = 'sub_sub_categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'sub_category_id',
        'slug',
        'meta_description',
        'order',
        'is_active',
        'sub_sub_category_image_path',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
