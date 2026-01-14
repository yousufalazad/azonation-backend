<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'notification_name_id',
        'is_active',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function notificationName()
    {
        return $this->belongsTo(NotificationName::class, 'notification_name_id');
    }

}
