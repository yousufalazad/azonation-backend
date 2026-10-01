<?php
namespace App\Http\Controllers\Org\Project;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Concerns\StoresAttachments;
use Illuminate\Routing\Controller;
use App\Models\Project;
use App\Models\ProjectAttendance;
use App\Models\ProjectGuestAttendance;
use App\Models\ProjectSummary;
use App\Models\ProjectSummaryFile;
use App\Models\ProjectSummaryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * The report on a project: what was achieved, who took part, who benefited, money, next steps.
 * Participation totals default to the count from the project's attendance records.
 */
class ProjectSummaryController extends Controller
{
    use ResolvesCurrentOrg, StoresAttachments;

    private const FILES = ['image' => ProjectSummaryImage::class, 'file' => ProjectSummaryFile::class];
    private const TEXT_FIELDS = [
        'summary', 'highlights', 'outcomes', 'feedback', 'challenges', 'suggestions', 'financial_overview', 'next_steps',
    ];

    public function __construct()
    {
        $this->middleware('org.permission:project-summary.read')->only(['index', 'show']);
        $this->middleware('org.permission:project-summary.create')->only(['create', 'store']);
        $this->middleware('org.permission:project-summary.update')->only(['edit', 'update']);
        $this->middleware('org.permission:project-summary.delete')->only(['destroy']);
    }

    public function index()
    {
        $summaries = $this->ownedVia(ProjectSummary::class, 'project_id', Project::class)
            ->select('project_summaries.*', 'projects.title as project_title', 'projects.start_date as project_start_date', 'projects.end_date as project_end_date')
            ->leftJoin('projects', 'project_summaries.project_id', '=', 'projects.id')
            ->get();
        return response()->json(['status' => true, 'data' => $summaries], 200);
    }

    public function show($id)
    {
        $summary = $this->ownedVia(ProjectSummary::class, 'project_id', Project::class)
            ->select(
                'project_summaries.*',
                'privacy_setups.name as privacy_setup_name',
                'projects.title as project_title',
                'projects.start_date as project_start_date',
                'projects.end_date as project_end_date'
            )
            ->leftJoin('privacy_setups', 'project_summaries.privacy_setup_id', '=', 'privacy_setups.id')
            ->leftJoin('projects', 'project_summaries.project_id', '=', 'projects.id')
            ->with(['images', 'documents'])
            ->where('project_summaries.id', $id)
            ->first();
        if (!$summary) {
            return response()->json(['status' => false, 'message' => 'Project summary not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $this->withAttachmentUrls($summary)], 200);
    }

    public function store(Request $request)
    {
        $this->ensureOwnedParent(Project::class, $request->input('project_id'));

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        try {
            $summary = new ProjectSummary();
            $summary->project_id = $request->project_id;
            $summary->created_by = $request->user()->id;
            $this->fill($summary, $request);
            $summary->save();
            $this->saveAttachments($request, $summary, self::FILES, 'project_summary_id', 'org/project-summary');
            return response()->json(['status' => true, 'data' => $summary, 'message' => 'Project summary created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating project summary: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if ($request->has('project_id')) $this->ensureOwnedParent(Project::class, $request->input('project_id'));

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $summary = $this->ownedVia(ProjectSummary::class, 'project_id', Project::class)->find($id);
        if (!$summary) {
            return response()->json(['status' => false, 'message' => 'Project summary not found'], 404);
        }
        try {
            $summary->project_id = $request->project_id;
            $this->fill($summary, $request);
            $summary->save();
            $this->saveAttachments($request, $summary, self::FILES, 'project_summary_id', 'org/project-summary');
            return response()->json(['status' => true, 'data' => $summary, 'message' => 'Project summary updated successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error updating project summary: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    public function destroy($id)
    {
        $summary = $this->ownedVia(ProjectSummary::class, 'project_id', Project::class)->find($id);
        if (!$summary) {
            return response()->json(['status' => false, 'message' => 'Project summary not found'], 404);
        }
        $this->deleteAttachments($summary);
        $summary->delete();
        return response()->json(['status' => true, 'message' => 'Project summary deleted successfully.'], 200);
    }

    private function rules(): array
    {
        $count = 'nullable|integer|min:0|max:100000000';
        return [
            'project_id' => 'required|integer|exists:projects,id',
            'privacy_setup_id' => 'required|integer|exists:privacy_setups,id',
            'summary' => 'nullable|string',
            'highlights' => 'nullable|string',
            'outcomes' => 'nullable|string',
            'feedback' => 'nullable|string',
            'challenges' => 'nullable|string',
            'suggestions' => 'nullable|string',
            'financial_overview' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'total_member_participation' => $count,
            'total_guest_participation' => $count,
            'total_beneficial_person' => $count,
            'total_communities_impacted' => $count,
            'total_expense' => $count,
            'is_publish' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ] + $this->attachmentRules();
    }

    private function fill(ProjectSummary $summary, Request $request): void
    {
        foreach (self::TEXT_FIELDS as $field) {
            $summary->{$field} = $request->input($field);
        }
        // Totals: what the person entered, otherwise the count of people marked as attended
        [$members, $guests] = $this->attendanceTotals((int) $request->project_id);
        $summary->total_member_participation = (int) ($request->input('total_member_participation') ?? $members);
        $summary->total_guest_participation = (int) ($request->input('total_guest_participation') ?? $guests);
        $summary->total_participation = $summary->total_member_participation + $summary->total_guest_participation;
        $summary->total_beneficial_person = (int) ($request->input('total_beneficial_person') ?? 0);
        $summary->total_communities_impacted = (int) ($request->input('total_communities_impacted') ?? 0);
        $summary->total_expense = (int) ($request->input('total_expense') ?? 0);
        $summary->privacy_setup_id = $request->privacy_setup_id;
        $summary->is_publish = $request->boolean('is_publish');
        $summary->is_active = $request->has('is_active') ? $request->boolean('is_active') : true;
        $summary->updated_by = $request->user()->id;
    }

    private function attendanceTotals(int $projectId): array
    {
        $count = fn (string $model, string $table) => $model::query()
            ->join('attendance_statuses', "$table.attendance_status_id", '=', 'attendance_statuses.id')
            ->where("$table.project_id", $projectId)
            ->where('attendance_statuses.is_attended', true)
            ->count();
        return [
            $count(ProjectAttendance::class, 'project_attendances'),
            $count(ProjectGuestAttendance::class, 'project_guest_attendances'),
        ];
    }
}
