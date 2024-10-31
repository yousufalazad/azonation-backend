<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'max_members',
        'storage_limit',
        'custom_branding',
        'api_access',
        'priority_support',
        'meeting_limit',
        'event_limit',
        'project_limit',
        'asset_limit',
        'document_limit',
        'report',
        'advanced_report',
        'custom_report',
        'support',
        'premium_support',
        'dedicated_account_manager',
        'custom_domain',
        'custom_email_template',
        'multi_currency_payment',
        'custom_username',
        'web_profile',
        'status'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
