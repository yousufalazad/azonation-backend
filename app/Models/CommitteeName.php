<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeName extends Model
{
    use HasFactory;
   
    protected $fillable = [
        'user_id',
        'name',
        'short_description',
        'start_date',
        'end_date',
        'note',
        'status'
    ];
}