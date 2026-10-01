<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * What happened with someone's attendance (Present, Late, Absent...).
 * is_attended: true when the person took part, false for Absent, Excused, No Show.
 */
class AttendanceStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'is_attended',
        'sort_order',
        'is_active'
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'is_attended' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
