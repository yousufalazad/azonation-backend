<?php

namespace App\Http\Controllers;

use App\Models\OfficeRecord;
use App\Models\OfficeRecordImage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class OfficeRecordController extends Controller
{
    // Fetch all organizational history records

    public function index()
    {
        try {
            // Fetch all office records with associated images
            $officeRecords = OfficeRecord::with('images')->get();

            return response()->json([
                'status' => true,
                'message' => 'Office records fetched successfully!',
                'data' => $officeRecords
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOfficeRecord($recordId)
    {
        // Find the meeting by ID
        $record = OfficeRecord::find($recordId);

        // Check if meeting exists
        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        // Return the meeting data
        return response()->json(['status' => true, 'data' => $record], 200);
    }

    // Store a new organizational history record
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'string|max:20000',
            'status' => 'required|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20048', // Image validation for each file
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10024', // Document validation
        ]);

        // Handle the document upload if present
        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentFile = $request->file(key: 'document');
            $documentPath = $documentFile->storeAs(
                'org/doc/office-record',
                Carbon::now()->format(format: 'YmdHis') . '_' . $documentFile->getClientOriginalName(),
                'public'
            );
        }

        // Create the new organization office record
        $OfficeRecord = new OfficeRecord();
        $OfficeRecord->user_id = $validatedData['user_id'];
        $OfficeRecord->title = $validatedData['title'];
        $OfficeRecord->description = $validatedData['description'];
        $OfficeRecord->status = $validatedData['status']; // Ensure you're saving the status as well
        $OfficeRecord->document = $documentPath; // Store the document path if available
        $OfficeRecord->save(); // Save the office record

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Store the image and get its path
                $imagePath = $image->storeAs(
                    'org/image/office-record',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );

                // Save each image path in the office_record_images table
                OfficeRecordImage::create([
                    'office_record_id' => $OfficeRecord->id,
                    'image' => $imagePath, // Store the image path
                ]);
            }
        }

        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'Organizational office record added successfully.',
            'data' => $OfficeRecord
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'string|max:20000',
            'status' => 'required|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20048',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10024',
        ]);

        // Find the existing organization office record
        $OfficeRecord = OfficeRecord::findOrFail($id);

        // Handle the document upload if present
        if ($request->hasFile('document')) {
            // Delete the old document if it exists
            if ($OfficeRecord->document) {
                Storage::delete('public/' . $OfficeRecord->document);
            }
            $documentFile = $request->file('document');
            $documentPath = $documentFile->storeAs(
                'org/doc/office-record',
                Carbon::now()->format('YmdHis') . '_' . $documentFile->getClientOriginalName(),
                'public'
            );
            $OfficeRecord->document = $documentPath; // Update the document path
        }

        // Update organization office record fields
        $OfficeRecord->user_id = $validatedData['user_id'];
        $OfficeRecord->title = $validatedData['title'];
        $OfficeRecord->description = $validatedData['description'];
        $OfficeRecord->status = $validatedData['status'];
        $OfficeRecord->save(); // Save the updated office record

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            // Delete old images
            $oldImages = OfficeRecordImage::where('office_record_id', $id)->get();
            foreach ($oldImages as $oldImage) {
                Storage::delete('public/' . $oldImage->image);
                $oldImage->delete();
            }
            // Upload and save new images
            foreach ($request->file('images') as $image) {
                // Store the image and get its path
                $imagePath = $image->storeAs(
                    'org/image/office-record',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    options: 'public'
                );
                // Save each image path in the office_record_images table
                OfficeRecordImage::create([
                    'office_record_id' => $OfficeRecord->id,
                    'image' => $imagePath, // Store the image path
                ]);
            }
        }

        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'Organizational office record updated successfully.',
            'data' => $OfficeRecord
        ], 200);
    }

    // Delete an organizational history record
    public function destroy($id)
    {
        try {
            $OfficeRecord = OfficeRecord::findOrFail($id);

            // Delete the image and document if they exist
            if ($OfficeRecord->document) {
                Storage::delete('public/' . $OfficeRecord->document);
            }

            // Delete old images
            $allImages = OfficeRecordImage::where('office_record_id', $id)->get();
            foreach ($allImages as $singleImage) {
                Storage::delete('public/' . $singleImage->image);
                $singleImage->delete();
            }

            $OfficeRecord->delete();

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