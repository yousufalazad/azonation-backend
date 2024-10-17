<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeRecordImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_office_record_id',
        'image',
    ];

    /**
     * Get the office record that owns the image.
     */
    public function officeRecord()
    {
        return $this->belongsTo(OrgOfficeRecord::class, 'org_office_record_id');
    }
}
