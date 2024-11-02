<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignmentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'responsible_user_id',
        'assignment_start_date',
        'assignment_end_date',
        'asset_lifecycle_statuses_id',
        'note',
        'is_active'
    ];

    /**
     * Get the asset associated with this assignment log.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
