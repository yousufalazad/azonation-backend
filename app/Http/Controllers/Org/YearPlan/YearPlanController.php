<?php

namespace App\Http\Controllers\Org\YearPlan;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Concerns\StoresAttachments;
use App\Http\Controllers\Controller;
use App\Models\YearPlan;
use App\Models\YearPlanFile;
use App\Models\YearPlanImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Plans for a year (or a few): goals, activities, budget and where the plan stands.
 */
class YearPlanController extends Controller
{
    use ResolvesCurrentOrg, StoresAttachments;

    private const FILES = ['image' => YearPlanImage::class, 'file' => YearPlanFile::class];
    private const STATUSES = ['draft', 'approved', 'completed', 'archived'];

    public function index()
    {
        // The current organisation's plans (not the signed-in person's own id)
        $plans = $this->owned(YearPlan::class)
            ->select('year_plans.*', 'privacy_setups.name as privacy_name')
            ->leftJoin('privacy_setups', 'year_plans.privacy_setup_id', '=', 'privacy_setups.id')
            ->orderBy('year_plans.start_year', 'desc')
            ->orderBy('year_plans.id', 'desc')
            ->get();
        return response()->json(['status' => true, 'data' => $plans], 200);
    }

    public function show($id)
    {
        $plan = $this->owned(YearPlan::class)
            ->select('year_plans.*', 'privacy_setups.name as privacy_name')
            ->leftJoin('privacy_setups', 'year_plans.privacy_setup_id', '=', 'privacy_setups.id')
            ->where('year_plans.id', $id)
            ->first();
        if (!$plan) {
            return response()->json(['status' => false, 'message' => 'Year plan not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $this->withAttachmentUrls($plan)], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $plan = new YearPlan($this->values($request));
        $plan->user_id = $this->orgIdOrFail(); // always the current organisation
        $plan->save();
        $this->saveAttachments($request, $plan, self::FILES, 'year_plan_id', 'org/year-plan');
        return response()->json(['status' => true, 'message' => 'Year plan created successfully.', 'data' => $plan], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $plan = $this->owned(YearPlan::class)->find($id);
        if (!$plan) {
            return response()->json(['status' => false, 'message' => 'Year plan not found'], 404);
        }
        $plan->update($this->values($request));
        // Files added while editing are saved too (they were ignored before)
        $this->saveAttachments($request, $plan, self::FILES, 'year_plan_id', 'org/year-plan');
        return response()->json(['status' => true, 'message' => 'Year plan updated successfully!', 'data' => $plan], 200);
    }

    public function destroy($id)
    {
        $plan = $this->owned(YearPlan::class)->find($id);
        if (!$plan) {
            return response()->json(['status' => false, 'message' => 'Year plan not found'], 404);
        }
        $this->deleteAttachments($plan);
        $plan->delete();
        return response()->json(['status' => true, 'message' => 'Year plan deleted successfully!'], 200);
    }

    private function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'start_year' => 'nullable|integer|digits:4|min:1901|max:2155',
            'end_year' => 'nullable|integer|digits:4|min:1901|max:2155|gte:start_year',
            'goals' => 'nullable|string|max:60000',
            'activities' => 'nullable|string|max:60000',
            'budget' => 'nullable|numeric|min:0|max:9999999999999',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'privacy_setup_id' => 'nullable|exists:privacy_setups,id',
            'published' => 'nullable|boolean',
            'status' => 'nullable|in:' . implode(',', self::STATUSES),
        ] + $this->attachmentRules();
    }

    private function values(Request $request): array
    {
        return [
            'title' => $request->title,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'goals' => $request->goals,
            'activities' => $request->activities,
            'budget' => $request->budget,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'privacy_setup_id' => $request->privacy_setup_id,
            'published' => $request->boolean('published'),
            'status' => $request->input('status') ?: 'draft',
        ];
    }
}
