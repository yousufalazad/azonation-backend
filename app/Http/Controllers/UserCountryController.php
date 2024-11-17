<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class UserCountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getUser()
    {
        $users = User::all();
        return response()->json(['status' => true, 'data' => $users], 200);
    }
    public function index()
    {
        $usersCountry = UserCountry::select('user_countries.*', 'users.name as user_name', 'countries.country_name as country_name')
            ->leftJoin('users', 'user_countries.user_id', '=', 'users.id')
            ->leftJoin('countries', 'user_countries.country_id', '=', 'countries.id')
            ->get();
        return response()->json(['status' => true, 'data' => $usersCountry], 200);
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
        // Validation
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'user_id' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('User Country data: ', ['country_id' => $request->country_id, 'user_id' => $request->user_id]);

            // Create the User Country record
            $dialingCode = UserCountry::create([
                'country_id' => $request->country_id,
                'user_id' => $request->user_id,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'User Country created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Country: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create User Country.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserCountry $userCountry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserCountry $userCountry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'user_id' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the dialingCode
        $dialingCode = UserCountry::find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'User Country not found.'], 404);
        }

        // Update the dialingCode
        $dialingCode->update([
            'country_id' => $request->country_id,
            'user_id' => $request->user_id,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'User Country updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $dialingCode = UserCountry::find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'User Country not found.'], 404);
        }

        $dialingCode->delete();
        return response()->json(['status' => true, 'message' => 'User Country deleted successfully.'], 200);
    }
}
