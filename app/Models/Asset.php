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
        'quantity',
        'value_amount',
        'inkind_value',
        'is_long_term',
        'is_tangible',
        'privacy_setup_id',
        'is_active'
    ];

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function assignmentLogs()
    {
        return $this->hasMany(AssetAssignmentLog::class, 'asset_id');
    }

    public function lifecycleStatus()
    {
        return $this->belongsTo(AssetLifecycleStatus::class, 'asset_lifecycle_statuses_id');
    }

    public function privacySetup()
    {
        return $this->belongsTo(PrivacySetup::class, 'privacy_setup_id');
    }

    public function documents()
    {
        return $this->hasMany(AssetFile::class, 'asset_id');
    }
    public function images()
    {
        return $this->hasMany(AssetImage::class, 'asset_id');
    }
}