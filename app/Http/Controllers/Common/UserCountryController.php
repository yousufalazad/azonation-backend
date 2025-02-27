<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\UserCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserCountryController extends Controller
{
    public function getUser()
    {
        $users = User::all();
        return response()->json(['status' => true, 'data' => $users], 200);
    }
    public function index()
    {
        $usersCountry = UserCountry::select('user_countries.*', 'users.name as user_name', 'countries.name as country_name')
            ->leftJoin('users', 'user_countries.user_id', '=', 'users.id')
            ->leftJoin('countries', 'user_countries.country_id', '=', 'countries.id')
            ->get();
        return response()->json(['status' => true, 'data' => $usersCountry], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'user_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('User Country data: ', ['country_id' => $request->country_id, 'user_id' => $request->user_id]);
            $dialingCode = UserCountry::create([
                'country_id' => $request->country_id,
                'user_id' => $request->user_id,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'User Country created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Country: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create User Country.'], 500);
        }
    }
    public function show(UserCountry $userCountry) {}
    public function edit(UserCountry $userCountry) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'user_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $dialingCode = UserCountry::find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'User Country not found.'], 404);
        }
        $dialingCode->update([
            'country_id' => $request->country_id,
            'user_id' => $request->user_id,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'User Country updated successfully.'], 200);
    }
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
