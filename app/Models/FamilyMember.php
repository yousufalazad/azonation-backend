<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'member_id',
        'name',
        'mobile',
        'email',
        'address',
        'day_month_of_birth',
        'gender',
        'relationship',
        'life_status',
        'is_active',
    ];

    protected $casts = [
        'day_month_of_birth' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
