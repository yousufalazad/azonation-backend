<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ManagementPackage;

class ManagementSubscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'management_package_id',
        'start_date',
        'subscription_status',
        'reason_for_action',
        'is_active'
    ];


    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function managementPackage()
    {
        return $this->belongsTo(ManagementPackage::class, 'management_package_id', 'id')->where('is_active', true);
    }
    
    
}
