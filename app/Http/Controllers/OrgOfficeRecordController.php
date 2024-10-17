<?php

namespace App\Http\Controllers;

use App\Models\OrgOfficeRecord;
use App\Models\OfficeRecordImage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class OrgOfficeRecordController extends Controller
{
    // Fetch all organizational history records

    public function index()
    {
        try {
            // Fetch all office records with associated images
            $officeRecords = OrgOfficeRecord::with('images')->get();

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

    // Store a new organizational history record
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:20000',
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
        $OrgOfficeRecord = new OrgOfficeRecord();
        $OrgOfficeRecord->user_id = $validatedData['user_id'];
        $OrgOfficeRecord->title = $validatedData['title'];
        $OrgOfficeRecord->description = $validatedData['description'];
        $OrgOfficeRecord->status = $validatedData['status']; // Ensure you're saving the status as well
        $OrgOfficeRecord->document = $documentPath; // Store the document path if available
        $OrgOfficeRecord->save(); // Save the office record

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
                    'org_office_record_id' => $OrgOfficeRecord->id,
                    'image' => $imagePath, // Store the image path
                ]);
            }
        }

        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'Organizational office record added successfully.',
            'data' => $OrgOfficeRecord
        ], 201);
    }
    
    // public function store(Request $request)
    // {
    //     // Validate the incoming request
    //     $validatedData = $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'title' => 'required|string|max:255',
    //         'description' => 'required|string|max:20000',
    //         'status' => 'required|integer',
    //         'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:512',
    //         'document' => 'nullable|file|mimes:pdf,doc,docx|max:512', // Validating document file
    //     ]);        

    //     // Handle the document upload if present
    //     $documentPath = null;
    //     if ($request->hasFile('document')) {
    //         $documentFile = $request->file('document');
    //         $documentPath = $documentFile->storeAs('org/docs/office-record', Carbon::now()->format('YmdHis') . '_' . $documentFile->getClientOriginalName(), 'public');
    //     }

    //     // Create the new organization description record
    //     $OrgOfficeRecord = new OrgOfficeRecord();
    //     $OrgOfficeRecord->user_id = $validatedData['user_id'];
    //     $OrgOfficeRecord->title = $validatedData['title'];
    //     $OrgOfficeRecord->description = $validatedData['description'];
    //     // $OrgOfficeRecord->status = $validatedData['status'];
    //     $OrgOfficeRecord->document = $documentPath; // Store the document path if available
    //     $OrgOfficeRecord->save(); // Save the record in the database

    //     // Handle multiple image uploads
    //     if ($request->hasFile('images')) {
    //         foreach ($request->file('images') as $image) {
    //             $imagePath = 'images/office-record' . now()->format('YmdHis') . '_' . $image->getClientOriginalName();
    //             Storage::put($imagePath, file_get_contents($image));

    //             // Save each image path in the office_record_images table
    //             OfficeRecordImage::create([
    //                 'org_office_record_id' => $OrgOfficeRecord->id,
    //                 'image' => $imagePath,
    //             ]);
    //         }
    //     }
    //     // Return a response, typically a JSON response for API-based applications
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Organizational history record added successfully.',
    //         'data' => $OrgOfficeRecord
    //     ], 201);
    // }

    // Update an existing organizational history record
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:20000',
            'status' => 'required|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:1024', // Validating document file
        ]);

        try {
            $OrgOfficeRecord = OrgOfficeRecord::findOrFail($id);

            // Handle the document upload if present
            if ($request->hasFile('document')) {
                // Delete the old document if present
                // if ($OrgOfficeRecord->document) {
                //     Storage::delete('public/' . $OrgOfficeRecord->document);
                // }
                $documentFile = $request->file('document');
                $OrgOfficeRecord->document = $documentFile->storeAs('org/docs/office-record', Carbon::now()->format('YmdHis') . '_' . $documentFile->getClientOriginalName(), 'public');
            }

            // Update other fields
            $OrgOfficeRecord->update([
                'user_id' => $validatedData['user_id'],
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                // 'status' => $validatedData['status'],
            ]);

            // Handle new images upload
            if ($request->hasFile('images')) {
                // Optionally, delete old images first
                $oldImages = OfficeRecordImage::where('org_office_record_id', $OrgOfficeRecord->id)->get();
                // foreach ($oldImages as $oldImage) {
                //     Storage::delete($oldImage->image);
                //     $oldImage->delete();
                // }

                // Save new images
                foreach ($request->file('images') as $image) {
                    $imagePath = 'images/office-record' . now()->format('YmdHis') . '_' . $image->getClientOriginalName();
                    Storage::put($imagePath, file_get_contents($image));

                    OfficeRecordImage::create([
                        'org_office_record_id' => $OrgOfficeRecord->id,
                        'image' => $imagePath,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Organizational history updated successfully.',
                'data' => $OrgOfficeRecord,
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
            $OrgOfficeRecord = OrgOfficeRecord::findOrFail($id);

            // Delete the image and document if they exist
            if ($OrgOfficeRecord->document) {
                Storage::delete('public/' . $OrgOfficeRecord->document);
            }

            $OrgOfficeRecord->delete();

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