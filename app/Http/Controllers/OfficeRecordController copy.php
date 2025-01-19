<?php

namespace App\Http\Controllers;

use App\Models\OfficeRecord;
use App\Models\OfficeRecordImage;
use App\Models\OfficeRecordDocument;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfficeRecordController extends Controller
{
    // Fetch all organizational history records

    public function index()
    {
        try {
            // Fetch all office records with associated images
            $officeRecords = OfficeRecord::with(['images', 'documents'])->get();

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

    public function show($recordId)
    {
        // Find the record by ID with related images and documents
        $record = OfficeRecord::with(['images', 'documents'])->find($recordId);

        // Check if the record exists
        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found'], 404);
        }

        // Map over the images to include their full URLs
        $record->images = $record->images->map(function ($image) {
            $image->image_url = $image->image_path
                ? url(Storage::url($image->image_path))
                : null;
            return $image;
        });

        // Map over the documents to include their full URLs
        $record->documents = $record->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });

        // Return the record data with transformed images and documents
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
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation for each file
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // Document validation
        ]);

        $user_id = $request->user()->id;

        // Start transaction
        DB::beginTransaction();

        try {
            // Create the new organization office record
            $officeRecord = new OfficeRecord();
            $officeRecord->title = $validatedData['title'];
            $officeRecord->description = $validatedData['description'];
            $officeRecord->privacy_setup_id = $validatedData['privacy_setup_id']; // Ensure you're saving the privacy setup
            $officeRecord->user_id = $user_id; // Logged in user ID
            $officeRecord->save();

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/office-record',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    OfficeRecordDocument::create([
                        'office_record_id' => $officeRecord->id,
                        'file_path' => $documentPath, // Store the document path
                        'file_name' => $document->getClientOriginalName(), // Store the document name
                        'mime_type' => $document->getClientMimeType(), // Store the MIME type
                        'file_size' => $document->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/image/office-record',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    OfficeRecordImage::create([
                        'office_record_id' => $officeRecord->id,
                        'image_path' => $imagePath, // Store the document path
                        'file_name' => $image->getClientOriginalName(), // Store the document name
                        'mime_type' => $image->getClientMimeType(), // Store the MIME type
                        'file_size' => $image->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // Commit transaction
            DB::commit();

            // Return a JSON response indicating success
            return response()->json([
                'status' => true,
                'message' => 'Organizational office record added successfully.',
                'data' => $officeRecord
            ], 201);
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollBack();

            // Return an error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {

        // dd($request->all());exit;
        // Validate the incoming request
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'string|max:20000',
            'privacy_setup_id' => 'nullable|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation for each file
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // Document validation
        ]);

        $user_id = $request->user()->id;

        // Start transaction
        DB::beginTransaction();

        try {
            // Find the organization office record to update
            $officeRecord = OfficeRecord::findOrFail($id);
            $officeRecord->title = $validatedData['title'];
            $officeRecord->description = $validatedData['description'];
            $officeRecord->privacy_setup_id = $validatedData['privacy_setup_id']; // Ensure you're saving the privacy setup
            $officeRecord->user_id = $user_id; // Logged in user ID
            $officeRecord->save();

            // Delete existing documents and images before adding new ones
            // OfficeRecordDocument::where('office_record_id', $officeRecord->id)->delete();
            // OfficeRecordImage::where('office_record_id', $officeRecord->id)->delete();

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/office-record',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    OfficeRecordDocument::create([
                        'office_record_id' => $officeRecord->id,
                        'file_path' => $documentPath, // Store the document path
                        'file_name' => $document->getClientOriginalName(), // Store the document name
                        'mime_type' => $document->getClientMimeType(), // Store the MIME type
                        'file_size' => $document->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/image/office-record',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    OfficeRecordImage::create([
                        'office_record_id' => $officeRecord->id,
                        'image_path' => $imagePath, // Store the document path
                        'file_name' => $image->getClientOriginalName(), // Store the document name
                        'mime_type' => $image->getClientMimeType(), // Store the MIME type
                        'file_size' => $image->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // Commit transaction
            DB::commit();

            // Return a JSON response indicating success
            return response()->json([
                'status' => true,
                'message' => 'Organizational office record updated successfully.',
                'data' => $officeRecord
            ], 200);
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollBack();

            // Return an error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
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
            $allDocuments = OfficeRecordDocument::where('office_record_id', $id)->get();
            foreach ($allDocuments as $singleDocument) {
                Storage::delete('public/' . $singleDocument->file_path);
                $singleDocument->delete();
            }

            // Delete old images
            $allImages = OfficeRecordImage::where('office_record_id', $id)->get();
            foreach ($allImages as $singleImage) {
                Storage::delete('public/' . $singleImage->image_path);
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
