<?php
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::id();
        $userNotifications = UserNotification::where('user_id', $userId)
            // ->with(['memberRenewalCycle'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'User notifications retrieved successfully.',
            'data' => $userNotifications
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //  `id`, `user_id`, `notification_name_id`, `is_active`, `created_at`, `updated_at`
        $request['user_id'] = Auth::id();
        $userNotification = UserNotification::create($request->all());
        return response()->json([
            'status' => true,
            'message' => 'User notification created successfully.',
            'data' => $userNotification
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserNotification $userNotification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserNotification $userNotification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $userNotification = UserNotification::findOrFail($id);
        $userNotification->update($request->all());
        return response()->json([
            'status' => true,
            'message' => 'User notification updated successfully.',
            'data' => $userNotification
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        $userNotification = UserNotification::findOrFail($id);
        $userNotification->delete();
        return response()->json([
            'status' => true,
            'message' => 'User notification deleted successfully.'
        ]);
    }
}