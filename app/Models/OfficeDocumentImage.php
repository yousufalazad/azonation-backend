<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeDocumentImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_document_id',
        'image_path',
        'file_name',
        'mime_type',
        'file_size',
        'is_public',  // Whether the profile image is publicly visible or not
        'is_active'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function officeDocument()
    {
        return $this->belongsTo(OfficeDocument::class, 'office_document_id');
    }
}
