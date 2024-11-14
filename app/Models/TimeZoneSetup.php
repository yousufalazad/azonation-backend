<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeZoneSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'time_zone', 
        'offset', 
        'description', 
        'is_active'
    ];
   

    //     'gmt_offset',
    //     'dst_offset',
        //     'is_dst',
        //     'abbreviation',
        //     'utc_offset',
        //     'is_dst_in_use',
        //     'dst_end_date',
        //     'dst_start_date',


    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
