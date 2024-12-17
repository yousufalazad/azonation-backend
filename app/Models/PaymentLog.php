<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'user_id',
        'user_name',
        'gateway',
        'transaction_id',
        'payment_status',
        'payment_method',
        'currency',
        'amount_paid',
        'exchange_rate',
        'note',
        'admin_note',
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];    
}
