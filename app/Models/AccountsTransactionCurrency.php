<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class AccountsTransactionCurrency extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'currency_id', 'is_active'];


    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
