<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

use App\Models\UserLanguage;
use App\Models\User;
use App\Models\UserCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserLanguageController extends Controller
{

    // getUserLanguage
    public function getUserLanguage(Request $request)
    {
        $userId = Auth::id();
        $userLanguage = UserLanguage::where('user_id', $userId)->with('userLanguageName')->first();
        // dd($userLanguage);exit;
        if ($userLanguage) {
            return response()->json(['status' => true, 'data' => $userLanguage]);
        } else {
            return response()->json(['status' => false, 'message' => 'User language not found']);
        }
    }
   
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $validator = Validator::make($request->all(), [
            'language_id' => 'required',
            'user_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('User Language data: ', ['language_id' => $request->language_id, 'user_id' => $request->user_id]);
            $userLanguage = UserLanguage::create([
                'language_id' => $request->language_id,
                'user_id' => $request->user_id,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $userLanguage, 'message' => 'User Language created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating User Language: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create User Language.'], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(UserLanguage $userLanguage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserLanguage $userLanguage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => 'required',
            'user_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $userLanguage = UserLanguage::find($id);
        if (!$userLanguage) {
            return response()->json(['status' => false, 'message' => 'User Language not found.'], 404);
        }
        $userLanguage->update([
            'language_id' => $request->language_id,
            'user_id' => $request->user_id,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $userLanguage, 'message' => 'User Language updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserLanguage $userLanguage)
    {
        //
    }
}