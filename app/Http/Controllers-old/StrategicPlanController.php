<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StrategicPlan;
use App\Models\StrategicPlanFile;
use App\Models\StrategicPlanImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StrategicPlanController extends Controller
{
    // Get list of all strategic plans
    public function index()
    {
        try {
            $strategicPlans = StrategicPlan::with('user:id,name')->get();
            return response()->json([
                'status' => true,
                'data' => $strategicPlans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch strategic plans.'
            ], 500);
        }
    }

    public function show($id)
    {
        $strategicPlan =  StrategicPlan::where('id', $id)
            // ->with(['images', 'documents'])
            ->first();

        // Check if meeting exists
        if (!$strategicPlan) {
            return response()->json(['status' => false, 'message' => 'Strategic Plan not found'], 404);
        }

        // Map over the images to include their full URLs
        $strategicPlan->images = $strategicPlan->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });

        // Map over the documents to include their full URLs
        $strategicPlan->documents = $strategicPlan->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });

        // Return the meeting data
        return response()->json(['status' => true, 'data' => $strategicPlan], 200);
    }
    // Create a new strategic plan
    public function store(Request $request)
    {
        // dd($request->all());exit;
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'plan' => 'required|string|max:5000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $strategicPlan = StrategicPlan::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'plan' => $request->plan,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                // image
            ]);

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/strategic-plan',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    StrategicPlanFile::create([
                        'strategic_plan_id' => $strategicPlan->id,
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
                        'org/image/strategic-plan',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    StrategicPlanImage::create([
                        'strategic_plan_id' => $strategicPlan->id,
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
                'message' => 'Strategic plan created successfully.',
                'data' => $strategicPlan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create strategic plan.'
            ], 500);
        }
    }

    // Update an existing strategic plan
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'plan' => 'required|string|max:5000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $strategicPlan = StrategicPlan::findOrFail($id);

            $strategicPlan->update([
                'title' => $request->title,
                'plan' => $request->plan,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
            ]);

            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/strategic-plan',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    StrategicPlanFile::create([
                        'strategic_plan_id' => $strategicPlan->id,
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
                        'org/image/strategic-plan',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    StrategicPlanImage::create([
                        'strategic_plan_id' => $strategicPlan->id,
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
                'message' => 'Strategic plan updated successfully.',
                'data' => $strategicPlan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update strategic plan.'
            ], 500);
        }
    }

    // Delete a strategic plan
    public function destroy($id)
    {
        try {
            $strategicPlan = StrategicPlan::findOrFail($id);
            $strategicPlan->delete();

            return response()->json([
                'status' => true,
                'message' => 'Strategic plan deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete strategic plan.'
            ], 500);
        }
    }
}