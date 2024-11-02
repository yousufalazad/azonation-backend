<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_long_term',
        'quantity',
        'value_amount',
        'inkind_value',
        'is_tangible',
        'privacy_setup_id',
        'is_active'
    ];

    /**
     * Get the assignment logs associated with the asset.
     */
    public function assignmentLogs()
    {
        return $this->hasMany(AssetAssignmentLog::class, 'asset_id');
    }
}