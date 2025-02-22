<?php

namespace App\Http\Controllers;

use App\Models\YearPlan;
use App\Models\YearPlanFile;
use App\Models\YearPlanImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
class YearPlanController extends Controller
{
    /**
     * Display a listing of the year plans.
     */
    public function index()
    {
        try {
            $yearPlans = YearPlan::all(); // Fetch all year plans
            return response()->json(['status' => true, 'data' => $yearPlans], 200);
        } catch (\Exception $e) {
            Log::error('Year Plan Index Error: ' . $e->getMessage()); // Log error
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    /**
     * Store a newly created year plan in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_year' => 'required|string|max:4',
            'end_year' => 'required|string|max:4',
            'goals' => 'nullable|string',
            'activities' => 'nullable|string',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
            'privacy_setup_id' => 'required|integer|in:1,2,3',
            'published' => 'required|boolean',
            'status' => 'required|integer|in:1,2,3,4',
        ]);
        DB::beginTransaction(); // check both table for input, if not possible then full come back 

        try {
            // Convert ISO date strings to 'Y-m-d' format
           // $validatedData['start_date'] = Carbon::parse($validatedData['start_date'])->format('Y-m-d');
            //$validatedData['end_date'] = Carbon::parse($validatedData['end_date'])->format('Y-m-d');

            $yearPlan = YearPlan::create($validatedData);
            // Handle document uploads
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/year-plan',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );

                    YearPlanFile::create([
                        'year_plan_id' => $yearPlan->id,
                        'file_path' => $documentPath, // Store the document path
                        'file_name' => $document->getClientOriginalName(), // Store the document name
                        'mime_type' => $document->getClientMimeType(), // Store the MIME type
                        'file_size' => $document->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }

            // // Handle multiple image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/image/year-plan',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );

                    YearPlanImage::create([
                        'year_plan_id' => $yearPlan->id,
                        'file_path' => $imagePath, // Store the document path
                        'file_name' => $image->getClientOriginalName(), // Store the document name
                        'mime_type' => $image->getClientMimeType(), // Store the MIME type
                        'file_size' => $image->getSize(), // Store the size of the document
                        'is_public' => true, // Set the document as public
                        'is_active' => true, // Set the document as active
                    ]);
                }
            }
            

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Asset created successfully.',
            ], 200);
            // return response()->json(['message' => 'Asset created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    /**
     * Display the specified year plan.
     */
    public function show($id)
    {
        $yearPlan =  YearPlan::where('id', $id)
            ->with(['images', 'documents'])
            ->first();

        // Check if meeting exists
        if (!$yearPlan) {
            return response()->json(['status' => false, 'message' => 'Strategic Plan not found'], 404);
        }

        // Map over the images to include their full URLs
        $yearPlan->images = $yearPlan->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });

        // Map over the documents to include their full URLs
        $yearPlan->documents = $yearPlan->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });

        return response()->json(['status' => true, 'data' => $yearPlan], 200);

    }

    /**
     * Update the specified year plan in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'start_year' => 'required|string|max:4',
            'end_year' => 'required|string|max:4',
            'goals' => 'nullable|string',
            'activities' => 'nullable|string',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date',
            'privacy_setup_id' => 'required|integer|in:1,2,3',
            'published' => 'required|boolean',
            'status' => 'required|integer|in:1,2,3,4',
        ]);

        try {
            $yearPlan = YearPlan::findOrFail($id);

            // Convert ISO date strings to 'Y-m-d' format
            //$validatedData['start_date'] = Carbon::parse($validatedData['start_date'])->format('Y-m-d');
            //$validatedData['end_date'] = Carbon::parse($validatedData['end_date'])->format('Y-m-d');

            $yearPlan->update($validatedData);
            return response()->json(['status' => true, 'message' => 'Year plan updated successfully!', 'data' => $yearPlan], 200);
        } catch (\Exception $e) {
            Log::error('Year Plan Update Error: ' . $e->getMessage()); // Log error
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    /**
     * Remove the specified year plan from storage.
     */
    public function destroy($id)
    {
        try {
            $yearPlan = YearPlan::findOrFail($id);
            $yearPlan->delete();
            return response()->json(['status' => true, 'message' => 'Year plan deleted successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Year Plan Delete Error: ' . $e->getMessage()); // Log error
            return response()->json(['status' => false, 'message' => 'Year plan not found or cannot be deleted.'], 404);
        }
    }
}