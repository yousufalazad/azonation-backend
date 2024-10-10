<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountFund extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        //'transaction_id',
        'account_fund_id',
        'transaction_date',
        'transaction_type',
        'transaction_amount',
        'description'
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
