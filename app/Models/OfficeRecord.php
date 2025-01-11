<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        // 'document',
        'privacy_setup_id'
    ];
    public function documents()
    {
        return $this->hasMany(OfficeRecordDocument::class, 'office_record_id');
    }
    public function images()
    {
        return $this->hasMany(OfficeRecordImage::class, 'office_record_id');
    }
}
