<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\User;

class NotificationController extends Controller
{
    public function getNotifications($userId)
    {
        if ($user = User::find($userId)) {
            $notifications = $user->notifications;
            return response()->json([
                'status' => true,
                'message' => 'Notifications marked as read successfully',
                'data' => $notifications
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Organisation or user not found',
            ], 404);
        }
    }
    public function markAllAsRead($userId)
    {
        $user = User::find($userId);
        $user->unreadNotifications->markAsRead();
        return response()->json([
            'status' => true,
            'message' => 'Notifications marked as read successfully',
        ]);
    }
    public function markAsRead($userId, $notificationId)
    {
        $user = User::find($userId);
        $notification = $user->unreadNotifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            return response()->json([
                'status' => true,
                'message' => 'Notification marked as read successfully',
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Notification not found or already read',
        ], 404);
    }
}
