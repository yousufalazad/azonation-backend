<?php

namespace App\Http\Controllers;

use App\Models\SuccessStory;
use App\Models\SuccessStoryFile;
use App\Models\SuccessStoryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class SuccessStoryController extends Controller
{
    // List all success stories (GET /get-records)
    public function index()
    {
        try {
            // Fetch all success stories with related user data
            $stories = SuccessStory::with('user:id,name')->get();
            return response()->json([
                'status' => true,
                'data' => $stories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    public function show($id)
    {
        $successStory =  SuccessStory::where('id', $id)
            ->with(['images', 'documents'])
            ->first();

        // Check if meeting exists
        if (!$successStory) {
            return response()->json(['status' => false, 'message' => 'Strategic Plan not found'], 404);
        }

        // Map over the images to include their full URLs
        $successStory->images = $successStory->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });

        // Map over the documents to include their full URLs
        $successStory->documents = $successStory->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });

        // Return the meeting data
        return response()->json(['status' => true, 'data' => $successStory], 200);
    }

    // Create a new success story (POST /create-record)
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'story' => 'required|string',
            'status' => 'required|boolean',
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate image file
        ]);

        try {
            // Handle image upload
            // $imagePath = null;
            // if ($request->hasFile('image')) {
            //     // Save image to storage and get the path
            //     $imagePath = $request->file('image')->store('images', 'public');
            // }

            // Create a new success story
            $story = new SuccessStory();
            $story->user_id = $request->user()->id; // Save the user who is adding the story
            $story->title = $validated['title'];
            $story->story = $validated['story'];
            $story->status = $validated['status'];
            // $story->image_path = $imagePath; // Store the image path in the database
            $story->save();

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/success-story',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    SuccessStoryFile::create([
                        'success_story_id' => $story->id,
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
                        'org/image/success-story',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    SuccessStoryImage::create([
                        'success_story_id' => $story->id,
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
                'message' => 'Success story created successfully.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    // Update an existing success story (PUT /update-record/{id})
    public function update(Request $request, $id)
    {
        // Validate input
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'story' => 'required|string',
            'status' => 'required|boolean',
        ]);

        try {
            // Find the success story by ID
            $story = SuccessStory::find($id);

            if (!$story) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record not found.'
                ], 404);
            }
            
            // Update story details
            $story->title = $validated['title'];
            $story->story = $validated['story'];
            $story->status = $validated['status'];
            $story->save();

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/success-story',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    SuccessStoryFile::create([
                        'success_story_id' => $story->id,
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
                        'org/image/success-story',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    SuccessStoryImage::create([
                        'success_story_id' => $story->id,
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
                'message' => 'Success story updated successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    // Delete a success story (DELETE /delete-record/{id})
    public function destroy($id)
    {
        try {
            // Find the success story by ID
            $story = SuccessStory::find($id);

            if (!$story) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record not found.'
                ], 404);
            }

            // Delete the success story
            $story->delete();

            return response()->json([
                'status' => true,
                'message' => 'Success story deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
