<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recognition; // Assuming the model is called Recognition

class RecognitionController extends Controller
{
    /**
     * Display a listing of the recognitions.
     */
    public function index()
    {
        try {
            $recognitions = Recognition::get();
            return response()->json([
                'status' => true,
                'data' => $recognitions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Store a newly created recognition in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'recognition_date' => 'required|date',
            'privacy_setup_id' => 'required|integer',
            'status' => 'required|integer',
        ]);

        try {
            $recognition = Recognition::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'description' => $request->description,
                'recognition_date' => $request->recognition_date,
                'privacy_setup_id' => $request->privacy_setup_id,
                'status' => $request->status,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Recognition created successfully',
                'data' => $recognition
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Catch error: An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Update the specified recognition.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'recognition_date' => 'required|date',
            'privacy_setup_id' => 'required|integer',
            'status' => 'required|integer',
        ]);

        try {
            $recognition = Recognition::where('id', $id)->first();

            if (!$recognition) {
                return response()->json([
                    'status' => false,
                    'message' => 'Recognition not found'
                ], 404);
            }

            $recognition->update([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'description' => $request->description,
                'recognition_date' => $request->recognition_date,
                'privacy_setup_id' => $request->privacy_setup_id,
                'status' => $request->status,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Recognition updated successfully',
                'data' => $recognition
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified recognition from the database.
     */
    public function destroy($id)
    {
        try {
            $recognition = Recognition::where('id', $id)->first();

            if (!$recognition) {
                return response()->json([
                    'status' => false,
                    'message' => 'Recognition not found'
                ], 404);
            }

            $recognition->delete();

            return response()->json([
                'status' => true,
                'message' => 'Recognition deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}