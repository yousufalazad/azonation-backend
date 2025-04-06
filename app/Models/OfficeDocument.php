<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeDocument extends Model
{
    use HasFactory;

    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        // 'document',
        'privacy_setup_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    
    public function documents()
    {
        return $this->hasMany(OfficeDocumentFile::class, 'office_document_id');
    }
    public function images()
    {
        return $this->hasMany(OfficeDocumentImage::class, 'office_document_id');
    }
}
