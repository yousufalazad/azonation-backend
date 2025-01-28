<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTransactionFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',   // ID of the related account
        'file_path',    // Path where the file is stored
        'file_name',    // Original name of the file
        'mime_type',    // MIME type (e.g., image/jpeg, application/pdf)
        'file_size',    // Size of the file in bytes
        'is_public',    // Boolean indicating if the file is public
        'is_active',    // Boolean indicating if the file is active
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
