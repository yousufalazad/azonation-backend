<?php
namespace App\Http\Controllers\Org\YearPlan;
use App\Http\Controllers\Controller;

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
    public function index()
    {
        try {
            $yearPlans = YearPlan::where('user_id', Auth::id())
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $yearPlans,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve records.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
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
        DB::beginTransaction();
        try {
            $yearPlan = YearPlan::create($validatedData);
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/year-plan/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    YearPlanFile::create([
                        'year_plan_id' => $yearPlan->id,
                        'file_path' => $documentPath,
                        'file_name' => $document->getClientOriginalName(),
                        'mime_type' => $document->getClientMimeType(),
                        'file_size' => $document->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/year-plan/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    YearPlanImage::create([
                        'year_plan_id' => $yearPlan->id,
                        'file_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Asset created successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }
    public function show($id)
    {
        $yearPlan =  YearPlan::where('id', $id)
            ->with(['images', 'documents'])
            ->first();
        if (!$yearPlan) {
            return response()->json(['status' => false, 'message' => 'Strategic Plan not found'], 404);
        }
        $yearPlan->images = $yearPlan->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });
        $yearPlan->documents = $yearPlan->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $yearPlan], 200);
    }
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
            $yearPlan->update($validatedData);
            return response()->json(['status' => true, 'message' => 'Year plan updated successfully!', 'data' => $yearPlan], 200);
        } catch (\Exception $e) {
            Log::error('Year Plan Update Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $yearPlan = YearPlan::findOrFail($id);
            $yearPlan->delete();
            return response()->json(['status' => true, 'message' => 'Year plan deleted successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Year Plan Delete Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Year plan not found or cannot be deleted.'], 404);
        }
    }
}
