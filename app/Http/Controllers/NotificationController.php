<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Individual;
use App\Models\Organisation;

class NotificationController extends Controller
{
    //Notification for add member, member mark as read
    public function getNotifications__($userId)
    {
       
        $notifications = $userId->notifications; // Fetch all notifications (read and unread)
            return response()->json([
                'status' => true,
                'message' => 'Notifications marked as read successfully',
                'data' => $notifications
            ]);
    }
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
    public function markAllAsRead()
    {
        
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json([
            'status' => true,
            'message' => 'Notifications marked as read successfully',
        ]);
    }
}
