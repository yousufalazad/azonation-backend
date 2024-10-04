<?php

namespace App\Http\Controllers;

use App\Models\OrgHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'history' => 'required|string|max:20000',
            'status' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:512', // Validating image file
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:1024', // Validating document file
        ]);

        // Handle the image upload if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imagePath = $imageFile->storeAs('org/logos', Carbon::now()->format('YmdHis') . '_' . $imageFile->getClientOriginalName(), 'public');
        }

        // Handle the document upload if present
        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentFile = $request->file('document');
            $documentPath = $documentFile->storeAs('org/docs', Carbon::now()->format('YmdHis') . '_' . $documentFile->getClientOriginalName(), 'public');
        }

        // Create the new organization history record
        $orgHistory = new OrgHistory();
        $orgHistory->user_id = $validatedData['user_id'];
        $orgHistory->title = $validatedData['title'];
        $orgHistory->history = $validatedData['history'];
        $orgHistory->status = $validatedData['status'];
        $orgHistory->image = $imagePath; // Store the image path if available
        $orgHistory->document = $documentPath; // Store the document path if available
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
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'history' => 'required|string|max:20000',
            'status' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:512', // Validating image file
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:1024', // Validating document file
        ]);

        try {
            $orgHistory = OrgHistory::findOrFail($id);

            // Handle the image upload if present
            if ($request->hasFile('image')) {
                // Delete the old image if present
                if ($orgHistory->image) {
                    Storage::delete('public/' . $orgHistory->image);
                }
                $imageFile = $request->file('image');
                $orgHistory->image = $imageFile->storeAs('org/logos', Carbon::now()->format('YmdHis') . '_' . $imageFile->getClientOriginalName(), 'public');
            }

            // Handle the document upload if present
            if ($request->hasFile('document')) {
                // Delete the old document if present
                if ($orgHistory->document) {
                    Storage::delete('public/' . $orgHistory->document);
                }
                $documentFile = $request->file('document');
                $orgHistory->document = $documentFile->storeAs('org/docs', Carbon::now()->format('YmdHis') . '_' . $documentFile->getClientOriginalName(), 'public');
            }

            // Update other fields
            $orgHistory->update([
                'user_id' => $validatedData['user_id'],
                'title' => $validatedData['title'],
                'history' => $validatedData['history'],
                'status' => $validatedData['status'],
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

            // Delete the image and document if they exist
            if ($orgHistory->image) {
                Storage::delete('public/' . $orgHistory->image);
            }
            if ($orgHistory->document) {
                Storage::delete('public/' . $orgHistory->document);
            }

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
