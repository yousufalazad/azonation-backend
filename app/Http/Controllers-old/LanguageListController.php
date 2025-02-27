<?php

namespace App\Http\Controllers;

use App\Models\LanguageList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class LanguageListController extends Controller
{
    /**a
     * Display a listing of the resource.
     */
    public function index()
    {
        $designation = LanguageList::all();
        return response()->json(['status' => true, 'data' => $designation], 200);
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
            'language_name' => 'required|string|max:255',
            'language_code' => 'required',
            'default' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('LanguageList data: ', ['language_name' => $request->language_name, 'language_code' => $request->language_code, 'default' => $request->default, 'is_active' => $request->is_active]);

            // Create the LanguageList record
            $designation = LanguageList::create([
                'language_name' => $request->language_name,
                'language_code' => $request->language_code,
                'default' => $request->default,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $designation, 'message' => 'LanguageList created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating LanguageList: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create LanguageList.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LanguageList $languageList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LanguageList $languageList)
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
            'language_name' => 'required|string|max:255',
            'language_code' => 'required',
            'default' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the designation
        $designation = LanguageList::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'LanguageList not found.'], 404);
        }

        // Update the designation
        $designation->update([
            'language_name' => $request->language_name,
            'language_code' => $request->language_code,
            'default' => $request->default,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $designation, 'message' => 'LanguageList updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $designation = LanguageList::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'LanguageList not found.'], 404);
        }

        $designation->delete();
        return response()->json(['status' => true, 'message' => 'LanguageList deleted successfully.'], 200);
    }
}
