<?php

namespace App\Http\Controllers\Org\StrategicPlan;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Concerns\StoresAttachments;
use App\Http\Controllers\Controller;
use App\Models\StrategicPlan;
use App\Models\StrategicPlanFile;
use App\Models\StrategicPlanImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Long-term plans (mission, goals, how to get there), usually for several years.
 */
class StrategicPlanController extends Controller
{
    use ResolvesCurrentOrg, StoresAttachments;

    private const FILES = ['image' => StrategicPlanImage::class, 'file' => StrategicPlanFile::class];

    public function index()
    {
        // The current organisation's plans (not the signed-in person's own id)
        $plans = $this->owned(StrategicPlan::class)
            ->select('strategic_plans.*', 'privacy_setups.name as privacy_name')
            ->leftJoin('privacy_setups', 'strategic_plans.privacy_setup_id', '=', 'privacy_setups.id')
            ->orderBy('strategic_plans.id', 'desc')
            ->get();
        return response()->json(['status' => true, 'data' => $plans], 200);
    }

    public function show($id)
    {
        $plan = $this->owned(StrategicPlan::class)
            ->select('strategic_plans.*', 'privacy_setups.name as privacy_name')
            ->leftJoin('privacy_setups', 'strategic_plans.privacy_setup_id', '=', 'privacy_setups.id')
            ->where('strategic_plans.id', $id)
            ->first();
        if (!$plan) {
            return response()->json(['status' => false, 'message' => 'Strategic Plan not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $this->withAttachmentUrls($plan)], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $plan = new StrategicPlan($this->values($request));
        $plan->user_id = $this->orgIdOrFail(); // always the current organisation
        $plan->save();
        $this->saveAttachments($request, $plan, self::FILES, 'strategic_plan_id', 'org/strategic-plan');
        return response()->json(['status' => true, 'message' => 'Strategic plan created successfully.', 'data' => $plan], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $plan = $this->owned(StrategicPlan::class)->find($id);
        if (!$plan) {
            return response()->json(['status' => false, 'message' => 'Strategic Plan not found'], 404);
        }
        // The owner never changes (before, an admin saving a plan made it theirs)
        $plan->update($this->values($request));
        $this->saveAttachments($request, $plan, self::FILES, 'strategic_plan_id', 'org/strategic-plan');
        return response()->json(['status' => true, 'message' => 'Strategic plan updated successfully.', 'data' => $plan], 200);
    }

    public function destroy($id)
    {
        $plan = $this->owned(StrategicPlan::class)->find($id);
        if (!$plan) {
            return response()->json(['status' => false, 'message' => 'Strategic Plan not found'], 404);
        }
        $this->deleteAttachments($plan);
        $plan->delete();
        return response()->json(['status' => true, 'message' => 'Strategic plan deleted successfully.'], 200);
    }

    private function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            // Formatted text (links make it long); the pages clean it before showing it
            'plan' => 'nullable|string|max:500000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'privacy_setup_id' => 'nullable|exists:privacy_setups,id',
            'status' => 'nullable|boolean', // 1 = active, 0 = switched off
        ] + $this->attachmentRules();
    }

    private function values(Request $request): array
    {
        return [
            'title' => $request->title,
            'plan' => $request->plan,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'privacy_setup_id' => $request->privacy_setup_id,
            'status' => $request->has('status') ? $request->boolean('status') : true,
        ];
    }
}
