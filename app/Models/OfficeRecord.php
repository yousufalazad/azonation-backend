<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'document',
        'privacy_setup_id',
        'is_active'
    ];
    public function images()
    {
        return $this->hasMany(OfficeRecordImage::class, 'office_record_id');
    }
}
