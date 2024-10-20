<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'user_id',
        'title',
        'account_fund_id',
        'transaction_date',
        'transaction_type',
        'transaction_amount',
        'balance',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
