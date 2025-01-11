<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeRecordDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_record_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'is_public',  // Whether the profile image is publicly visible or not
        'is_active'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    // public function officeRecordDocuments()
    // {
    //     return $this->belongsTo(OfficeRecord::class, 'office_record_id');
    // }
}
