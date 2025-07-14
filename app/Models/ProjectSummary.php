<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'total_member_participation',
        'total_guest_participation',
        'total_participation',
        'total_beneficial_person',
        'total_communities_impacted',
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
        'outcomes',
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

    public function documents()
    {
        return $this->hasMany(ProjectSummaryFile::class, 'project_summary_id');
    }
    public function images()
    {
        return $this->hasMany(ProjectSummaryImage::class, 'project_summary_id');
    }
}
