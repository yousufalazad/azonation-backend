<?php

namespace App\Http\Controllers;

use App\Models\OfficeRecord;
use App\Models\OfficeRecordImage;
use App\Models\OfficeRecordDocument;

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
            'title' => 'required|string|max:255',
            'description' => 'string|max:20000',
            'privacy_setup_id' => 'nullable|integer',
            'image_path.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20048', // Image validation for each file
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10024', // Document validation
        ]);

        $user_id = $request->user()->id;

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
        $officeRecord = new OfficeRecord();
        $officeRecord->title = $validatedData['title'];
        $officeRecord->description = $validatedData['description'];
        $officeRecord->privacy_setup_id = $validatedData['privacy_setup_id']; // Ensure you're saving the status as well
        $officeRecord->document = $documentPath; // Store the document path if available
        $officeRecord->user_id = $user_id; // Logged in user ID
        $officeRecord->save(); // Save the office record

        $officeRecordDocument = new OfficeRecordDocument();
        $officeRecordDocument->office_record_id = $officeRecord->id;
        $officeRecordDocument->file_path = $documentPath; // Store the document path in the office_record_documents table
        $officeRecordDocument->file_name = $documentFile->getClientOriginalName(); // Store the document name
        $officeRecordDocument->mime_type = $documentFile->getClientMimeType(); // Store the MIME type of the document
        $officeRecordDocument->file_size = $documentFile->getSize(); // Store the size of the document
        $officeRecordDocument->is_public = true; // Set the document as public
        $officeRecordDocument->is_active = true; // Set the document as active
        $officeRecordDocument->save(); // Save the document related to the office record

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
                    'office_record_id' => $officeRecord->id,
                    'image_path' => $imagePath, // Store the image path
                ]);
            }
        }

        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'Organizational office record added successfully.',
            'data' => $officeRecord
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'string|max:20000',
            'privacy_setup_id' => 'nullable|integer',
            'image_path.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20048', // Image validation for each file
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10024', // Document validation
        ]);

        // Find the existing organization office record
        $officeRecord = OfficeRecord::findOrFail($id);

        // Handle the document upload if present
        if ($request->hasFile('document')) {
            // Delete the old document if it exists
            if ($officeRecord->document) {
                Storage::delete('public/' . $officeRecord->document);
            }
            $documentFile = $request->file('document');
            $documentPath = $documentFile->storeAs(
                'org/doc/office-record',
                Carbon::now()->format('YmdHis') . '_' . $documentFile->getClientOriginalName(),
                'public'
            );
            $officeRecord->document = $documentPath; // Update the document path
        }

        $user_id = $request->user()->id;

        // Update organization office record fields
        $officeRecord->title = $validatedData['title'];
        $officeRecord->description = $validatedData['description'];
        //$officeRecord->privacy_setup_id = $validatedData['privacy_setup_id']; // Ensure you're saving the status as well
        $officeRecord->document = $documentPath; // Store the document path if available
        $officeRecord->user_id = $user_id; // Logged in user ID
        $officeRecord->save(); // Save the updated office record

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
                    'office_record_id' => $officeRecord->id,
                    'image_path' => $imagePath, // Store the image path
                ]);
            }
        }

        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'Organizational office record updated successfully.',
            'data' => $officeRecord
        ], 200);
    }

    // Delete an organizational history record
    public function destroy($id)
    {
        try {
            $officeRecord = OfficeRecord::findOrFail($id);

            // Delete the image and document if they exist
            if ($officeRecord->document) {
                Storage::delete('public/' . $officeRecord->document);
            }

            // Delete old images
            $allImages = OfficeRecordImage::where('office_record_id', $id)->get();
            foreach ($allImages as $singleImage) {
                Storage::delete('public/' . $singleImage->image);
                $singleImage->delete();
            }

            $officeRecord->delete();

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