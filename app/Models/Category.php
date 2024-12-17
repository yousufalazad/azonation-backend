<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

     /**
     * The table associated with the model.
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'business_type_id',
        'slug',
        'meta_description',
        'order',
        'is_active',
        'category_image_path',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
