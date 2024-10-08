<?php

namespace App\Http\Controllers;

use App\Models\SuccessStory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    // Create a new success story (POST /create-record)
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'user_id' => 'required',
            'title' => 'required|string|max:255',
            'story' => 'required|string',
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate image file
        ]);

        try {
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                // Save image to storage and get the path
                $imagePath = $request->file('image')->store('images', 'public');
            }

            // Create a new success story
            $story = new SuccessStory();
            $story->user_id = $validated['user_id']; // Save the user who is adding the story
            $story->title = $validated['title'];
            $story->story = $validated['story'];
            $story->status = $validated['status'];
            $story->image_path = $imagePath; // Store the image path in the database
            $story->save();

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
            'user_id' => 'required',
            'title' => 'required|string|max:255',
            'story' => 'required|string',
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate image file
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

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($story->image) {
                    Storage::disk('public')->delete($story->image);
                }

                // Save the new image
                $imagePath = $request->file('image')->store('images', 'public');
                $story->image = $imagePath;
            }

            // Update story details
            $story->title = $validated['title'];
            $story->story = $validated['story'];
            $story->status = $validated['status'];
            $story->save();

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

            // Delete the image from storage if it exists
            if ($story->image_path) {
                Storage::disk('public')->delete($story->image_path);
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
