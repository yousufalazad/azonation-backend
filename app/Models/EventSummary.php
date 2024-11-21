<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_event_id',
        'total_member_attendance',
        'total_guest_attendance',
        'summary',
        'highlights',
        'feedback',
        'challenges',
        'suggestions',
        'financial_overview',
        'total_expense',
        'image_attachment',
        'file_attachment',
        'next_steps',
        'created_by',
        'updated_by',
        'privacy_setup_id',
        'is_active',
        'is_publish',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
