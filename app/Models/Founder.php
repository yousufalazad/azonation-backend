<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Founder extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 
        'founder_user_id', //individual type user_id
        'name',
        'designation'
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    public function founders()
{
    
    return $this->belongsTo(User::class, 'founder_user_id');
}

}