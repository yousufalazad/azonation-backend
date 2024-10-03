<?php

namespace App\Http\Controllers;

use App\Models\SuccessStory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'story' => 'required|string|max:5000',
            'status' => 'required|boolean',
        ]);

        try {
            // Create a new success story
            $story = new SuccessStory();
            $story->user_id = $validated['user_id']; // Save the user who is adding the story
            $story->title = $validated['title'];
            $story->story = $validated['story'];
            $story->status = $validated['status'];
            // image
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
