<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgOfficeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'document',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the images associated with this office record.
     */
    public function images()
    {
        return $this->hasMany(OfficeRecordImage::class, 'org_office_record_id');
    }
}