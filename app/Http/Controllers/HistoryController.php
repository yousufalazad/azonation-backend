<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\HistoryFile;
use App\Models\HistoryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoryController extends Controller
{
    // Fetch all organizational history records
    public function index()
    {
        try {
            $histories = History::with('user:id,name')->get();
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

    public function show($id)
    {
        $history = History::where('id', $id)
            ->with(['images', 'documents'])
            ->first();

        // Check if meeting exists
        if (!$history) {
            return response()->json(['status' => false, 'message' => 'History not found'], 404);
        }

        // Map over the images to include their full URLs
        $history->images = $history->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });

        // Map over the documents to include their full URLs
        $history->documents = $history->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });

        // Return the meeting data
        return response()->json(['status' => true, 'data' => $history], 200);
    }


    // Store a new organizational history record
    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'history' => 'required|string|max:20000',
            'status' => 'required|integer',
        ]);
        
        // Create the new organization history record
        $history = new History();
        $history->user_id = $request->user()->id;
        $history->title = $validatedData['title'];
        $history->history = $validatedData['history'];
        $history->status = $validatedData['status'];
        $history->save(); // Save the record in the database


        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPath = $document->storeAs(
                    'org/doc/history',
                    Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                    'public'
                );

                HistoryFile::create([
                    'history_id' => $history->id,
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
                    'org/image/history',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );

                HistoryImage::create([
                    'history_id' => $history->id,
                    'file_path' => $imagePath, // Store the document path
                    'file_name' => $image->getClientOriginalName(), // Store the document name
                    'mime_type' => $image->getClientMimeType(), // Store the MIME type
                    'file_size' => $image->getSize(), // Store the size of the document
                    'is_public' => true, // Set the document as public
                    'is_active' => true, // Set the document as active
                ]);
            }
        }

        // Return a response, typically a JSON response for API-based applications
        return response()->json([
            'status' => true,
            'message' => 'Organizational history record added successfully.',
            'data' => $history
        ], 201);
    }

    // Update an existing organizational history record
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'history' => 'required|string|max:20000',
            'status' => 'required|integer',
        ]);

        try {
            $history = History::findOrFail($id);


            // Update other fields
            $history->update([
                'title' => $validatedData['title'],
                'history' => $validatedData['history'],
                'status' => $validatedData['status'],
            ]);

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/history',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    HistoryFile::create([
                        'history_id' => $history->id,
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
                        'org/image/history',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    HistoryImage::create([
                        'history_id' => $history->id,
                        'file_path' => $imagePath, // Store the document path
                        'file_name' => $image->getClientOriginalName(), // Store the document name
                        'mime_type' => $image->getClientMimeType(), // Store the MIME type
                        'file_size' => $image->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Organizational history updated successfully.',
                'data' => $history,
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
            $history = History::findOrFail($id);

            // Delete the image and document if they exist
            if ($history->image) {
                Storage::delete('public/' . $history->image);
            }
            if ($history->document) {
                Storage::delete('public/' . $history->document);
            }

            $history->delete();

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
