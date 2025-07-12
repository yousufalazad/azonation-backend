<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneNumber extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'dialing_code_id',    
        'phone_number',    
        'phone_type',    
        'status',    
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    public function dialingCode()
    {
        return $this->belongsTo(DialingCode::class, 'dialing_code_id', 'id');
    }
}