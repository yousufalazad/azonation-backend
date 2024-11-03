<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'invoice_id',
        'billing_id',
        'generate_date',
        'issue_date',
        'due_date',
        'sub_total',
        'discount_title',
        'discount',
        'tax',
        'credit',
        'total',
        'note',
        'description',
        'published',
        'status',
        'action_status_reason',
        'hidden_admin_note'
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
