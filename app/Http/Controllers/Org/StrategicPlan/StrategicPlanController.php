<?php

namespace App\Http\Controllers\Org\StrategicPlan;

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
use Illuminate\Support\Facades\Auth;

class StrategicPlanController extends Controller
{
    public function index()
    {
        try {
            $strategicPlans = StrategicPlan::select('strategic_plans.*', 'privacy_setups.name as privacy_name')
                ->leftJoin('privacy_setups', 'strategic_plans.privacy_setup_id', '=', 'privacy_setups.id')
                ->where('strategic_plans.user_id', Auth::id())
                ->orderBy('strategic_plans.id', 'desc')
                ->get();
                // ->with('user:id,name')->get();

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
        $strategicPlan =  StrategicPlan::select('strategic_plans.*', 'privacy_setups.name as privacy_name')
            ->leftJoin('privacy_setups', 'strategic_plans.privacy_setup_id', '=', 'privacy_setups.id')
            ->where('strategic_plans.id', $id)
            ->first();
        if (!$strategicPlan) {
            return response()->json(['status' => false, 'message' => 'Strategic Plan not found'], 404);
        }
        $strategicPlan->images = $strategicPlan->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });
        $strategicPlan->documents = $strategicPlan->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $strategicPlan], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'plan' => 'nullable|string|max:5000',
            'start_date' => 'nullable',
            'end_date' => 'nullable',
            'privacy_setup_id' => 'nullable|exists:privacy_setups,id',
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
                'privacy_setup_id' => $request->privacy_setup_id,
                'status' => $request->status,
            ]);
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/strategic-plan/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    StrategicPlanFile::create([
                        'strategic_plan_id' => $strategicPlan->id,
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
                        'org/strategic-plan/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    StrategicPlanImage::create([
                        'strategic_plan_id' => $strategicPlan->id,
                        'file_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
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
    public function update(Request $request, $id)
    {
        // dd($request->all());exit;
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'plan' => 'nullable|string|max:5000',
            'start_date' => 'nullable',
            'end_date' => 'nullable',
            'privacy_setup_id' => 'nullable|exists:privacy_setups,id',
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
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'plan' => $request->plan,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'privacy_setup_id' => $request->privacy_setup_id,
                'status' => $request->status,
            ]);
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/strategic-plan/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    StrategicPlanFile::create([
                        'strategic_plan_id' => $strategicPlan->id,
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
                        'org/strategic-plan/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    StrategicPlanImage::create([
                        'strategic_plan_id' => $strategicPlan->id,
                        'file_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
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
