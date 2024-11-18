<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'package_id',
        'start_date',
        'end_date',
        'status'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    
    public function package()
{
    return $this->belongsTo(Package::class);
}

}
