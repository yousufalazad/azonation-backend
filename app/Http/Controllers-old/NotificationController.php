<?php

namespace App\Http\Controllers;
use App\Models\User;


class NotificationController extends Controller
{
    //Notification for add member, member mark as read
   
    public function getNotifications($userId)
    {
        //$authUserId = Auth::user()->id;
        if ($user = User::find($userId)) {
            $notifications = $user->notifications; // Fetch all notifications (read and unread)
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
    // public function markAsRead()
    // {
    //     Auth::user()->unreadNotifications->markAsRead();
    //     return redirect()->back();
    // }
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
            // Mark the notification as read
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
