<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class OrgAdministrator extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_type_user_id',
        'individual_type_user_id',
        'first_name',
        'last_name',
        'start_date',
        'end_date',
        'admin_note',
        'is_primary',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function individualUser()
    {
        return $this->belongsTo(User::class, 'individual_type_user_id', 'id');
    }

    public function orgUser()
    {
        return $this->belongsTo(User::class, 'org_type_user_id', 'id');
    }

     public function administratorProfileImage(): BelongsTo
    {
        return $this->belongsTo(ProfileImage::class, 'individual_type_user_id', 'user_id');
    }
}
