<?php
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

use App\Models\NotificationName;
use Illuminate\Http\Request;

class NotificationNameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notificationNames = NotificationName::all();
        return response()->json(['status' => true, 'data' => $notificationNames], 200);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(NotificationName $notificationName)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NotificationName $notificationName)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NotificationName $notificationName)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NotificationName $notificationName)
    {
        //
    }
}
