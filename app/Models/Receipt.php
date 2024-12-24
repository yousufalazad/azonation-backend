<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;
    protected $fillable = [
        'receipt_code',
        'invoice_id',
        'user_id',
        'amount_received',
        'payment_method',
        'transaction_reference',
        'payment_date',
        'note',
        'status',
        'admin_note',
        'is_published',
    ];
    
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}