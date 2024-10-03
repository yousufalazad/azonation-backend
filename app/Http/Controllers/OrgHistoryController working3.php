<?php

namespace App\Http\Controllers;

use App\Models\OrgHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrgHistoryController extends Controller
{
    // Fetch all organizational history records
    public function index()
    {
        try {
            $histories = OrgHistory::with('user:id,name')->get();
            return response()->json([
                'status' => true,
                'data' => $histories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve records.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Store a new organizational history record
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required|exists:users,id',
    //         'title' => 'required|string|max:255',
    //         // 'image'
    //         'history' => 'required|string|max:10000',
    //         'status' => 'required|boolean',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors(),
    //         ], 400);
    //     }

    //     try {
    //         $orgHistory = OrgHistory::create([
    //             'user_id' => $request->user_id,
    //             'title' => $request->title,
    //             'history' => $request->history,
    //             'status' => $request->status,
    //             // 'image' => $request->image,
    //         ]);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Organizational history created successfully.',
    //             'data' => $orgHistory,
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to create record.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'history' => 'required|string',
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validating image file
        ]);

        // Handle the image upload if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Store the image in the 'public/org_histories' directory and get the file path
            $imagePath = $request->file('image')->store('org_histories', 'public');
        }

        // Create the new organization history record
        $orgHistory = new OrgHistory();
        $orgHistory->user_id = $validatedData['user_id'];
        $orgHistory->title = $validatedData['title'];
        $orgHistory->history = $validatedData['history'];
        $orgHistory->status = $validatedData['status'];
        $orgHistory->image = $imagePath; // Store the image path if available
        $orgHistory->save(); // Save the record in the database

        // Return a response, typically a JSON response for API-based applications
        return response()->json([
            'status' => true,
            'message' => 'Organizational history record added successfully.',
            'data' => $orgHistory
        ], 201);
    }

    // Update an existing organizational history record
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'history' => 'required|string',
            'status' => 'required|boolean',
            // 'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Optional validation for image
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        try {
            $orgHistory = OrgHistory::findOrFail($id);
    
            // Check if an image was uploaded
            // if ($request->hasFile('image')) {
            //     $image = $request->file('image');
            //     $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            //     $extension = $image->getClientOriginalExtension();
            //     $timestamp = Carbon::now()->format('YmdHis');
            //     $newFileName = $timestamp . '_' . $originalName . '.' . $extension;
    
            //     $path = $image->storeAs('org/history', $newFileName, 'public');
            //     $orgHistory->image = $path; // Update the image path only if image is uploaded
            // }
    
            // Update other fields
            $orgHistory->update([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'history' => $request->history,
                'status' => $request->status,
                // 'image' => $orgHistory->image,
            ]);
    
            return response()->json([
                'status' => true,
                'message' => 'Organizational history updated successfully.',
                'data' => $orgHistory,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // Delete an organizational history record
    public function destroy($id)
    {
        try {
            $orgHistory = OrgHistory::findOrFail($id);
            $orgHistory->delete();

            return response()->json([
                'status' => true,
                'message' => 'Organizational history deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}