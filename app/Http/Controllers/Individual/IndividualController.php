<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Models\Individual;
use App\Models\OrgMember;
use App\Models\Meeting;
use App\Models\Event;
use App\Models\CommitteeMember;
use App\Models\Project;
use App\Models\AssetAssignmentLog;
use App\Models\ProfileImage;
use App\Models\EventAttendance;
use App\Models\MeetingAttendance;
use App\Models\ProjectAttendance;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class IndividualController extends Controller
{
    public function summary()
    {
        $userId = Auth::id();

        // Get connected organisations
        $connectedOrgs = OrgMember::with(['membershipType:id,name'])
            ->where('individual_type_user_id', $userId)
            ->leftJoin('users as connectedorg', 'org_members.org_type_user_id', '=', 'connectedorg.id')
            ->select('org_members.*', 'connectedorg.org_name')
            ->get();

        $hasConnection = $connectedOrgs->isNotEmpty();

        // Group committee memberships by organisation
        $committeesByOrg = CommitteeMember::with('committee:id,name,user_id,start_date')
            ->where('user_id', $userId)
            ->get()
            ->groupBy(fn($item) => optional($item->committee)->user_id);

        // Get all active asset assignments for the user
        // $assignmentLogs = AssetAssignmentLog::with(['asset:id,name,quantity,user_id', 'lifecycle:id,name as lifecyclestaus'])
        //     ->where('responsible_user_id', $userId)
        //     ->where('is_active', true)
        //     ->get()
        //     ->groupBy(fn($log) => optional($log->asset)->user_id); // Group by organisation ID

        $assignmentLogs = AssetAssignmentLog::with([
            'asset:id,name,quantity,user_id',
            'lifecycle:id,name'                  // eager load lifecycle status
        ])
            ->where('responsible_user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->groupBy(fn($log) => optional($log->asset)->user_id); // group by org ID

        $orgWiseData = [];

        foreach ($connectedOrgs as $org) {
            $orgId = $org->org_type_user_id;

            // Fetch next 2 meetings
            $nextMeetings = Meeting::where('user_id', $orgId)
                ->where('date', '>=', now())
                ->orderBy('date')
                ->limit(2)
                ->get();

            // Fetch next 2 events
            $upcomingEvents = Event::where('user_id', $orgId)
                ->where('date', '>=', now())
                ->orderBy('date')
                ->limit(2)
                ->get();

            // Fetch next 2 projects
            $upcomingProjects = Project::where('user_id', $orgId)
                ->where('start_date', '>=', now())
                ->orderBy('start_date')
                ->limit(2)
                ->get();

            // Committees
            $committees = $committeesByOrg[$orgId] ?? collect();

            // Responsible Assets
            $responsibleAssets = $assignmentLogs[$orgId] ?? collect();
            $assetsFormatted = $responsibleAssets->map(function ($log) {
                return [
                    'asset_id' => $log->asset_id,
                    'name' => optional($log->asset)->name,
                    'quantity' => optional($log->asset)->quantity,
                    'assignment_start_date' => $log->assignment_start_date,
                    'asset_lifecycle_status_id' => $log->asset_lifecycle_statuses_id,
                    'asset_lifecycle_status_name' => optional($log->lifecycle)->name, // Include lifecycle status name
                ];
            })->values();

            $orgWiseData[] = [
                'org_id' => $orgId,
                'org_name' => $org->org_name,
                'next_meetings' => $nextMeetings,
                'upcoming_events' => $upcomingEvents,
                'upcoming_projects' => $upcomingProjects,
                'committees' => $committees->map(function ($member) {
                    return [
                        'id' => optional($member->committee)->id,
                        'name' => optional($member->committee)->name,
                        'start_date' => optional($member->committee)->start_date,
                        'designation_id' => $member->designation_id,
                    ];
                })->values(),
                'responsible_assets' => $assetsFormatted,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => [
                'has_connection' => $hasConnection,
                'connected_organisations' => $connectedOrgs,
                'organisations_summary' => $orgWiseData,
            ],
        ]);
    }

    public function committees()
    {
        $userId = Auth::id();

        // Join committee data and include org name from users
        $committees = CommitteeMember::with('committee:id,name,user_id')
            ->where('user_id', $userId)
            ->whereRaw('(end_date >= ? OR end_date IS NULL)', [now()])
            ->get()
            ->groupBy(fn($member) => optional($member->committee)->user_id);

        // Get all related org names
        $orgs = OrgMember::with('org:id,org_name')
            ->where('individual_type_user_id', $userId)
            ->get();

        $result = $orgs->map(function ($org) use ($committees) {
            return [
                'org_id' => $org->org_type_user_id,
                'org_name' => optional($org->org)->org_name ?? 'Unknown',
                'committees' => $committees[$org->org_type_user_id] ?? [],
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }
    public function past_committees()
    {
        $userId = Auth::id();

        // Join committee data and include org name from users
        $committees = CommitteeMember::with('committee:id,name,user_id')
            ->where('user_id', $userId)
            ->where('end_date', '<=', now())
            ->get()
            ->groupBy(fn($member) => optional($member->committee)->user_id);

        // Get all related org names
        $orgs = OrgMember::with('org:id,org_name')
            ->where('individual_type_user_id', $userId)
            ->get();

        $result = $orgs->map(function ($org) use ($committees) {
            return [
                'org_id' => $org->org_type_user_id,
                'org_name' => optional($org->org)->org_name ?? 'Unknown',
                'committees' => $committees[$org->org_type_user_id] ?? [],
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }

    public function meetings()
    {
        $userId = Auth::id();

        $orgs = OrgMember::with('org:id,org_name')
            ->where('individual_type_user_id', $userId)
            ->get();

        $orgIds = $orgs->pluck('org_type_user_id');

        $meetings = Meeting::whereIn('user_id', $orgIds)
            ->where('date', '>=', now())
            ->orderBy('date')
            ->get()
            ->groupBy('user_id');

        $result = $orgs->map(function ($org) use ($meetings) {
            return [
                'org_id' => $org->org_type_user_id,
                'org_name' => optional($org->org)->org_name ?? 'Unknown',
                'meetings' => $meetings[$org->org_type_user_id] ?? [],
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }
    public function past_meetings()
    {
        $userId = Auth::id();

        $orgs = OrgMember::with('org:id,org_name')
            ->where('individual_type_user_id', $userId)
            ->get();

        $orgIds = $orgs->pluck('org_type_user_id');

        $meetings = Meeting::whereIn('user_id', $orgIds)
            ->where('date', '<=', now())
            ->orderBy('date')
            ->get()
            ->groupBy('user_id');

        $result = $orgs->map(function ($org) use ($meetings) {
            return [
                'org_id' => $org->org_type_user_id,
                'org_name' => optional($org->org)->org_name ?? 'Unknown',
                'meetings' => $meetings[$org->org_type_user_id] ?? [],
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }

    public function events()
    {
        $userId = Auth::id();

        $orgs = OrgMember::with('org:id,org_name')
            ->where('individual_type_user_id', $userId)
            ->get();

        $orgIds = $orgs->pluck('org_type_user_id');

        $events = Event::whereIn('user_id', $orgIds)
            ->where('date', '<=', now())
            ->orderBy('date')
            ->get()
            ->groupBy('user_id');

        $result = $orgs->map(function ($org) use ($events) {
            return [
                'org_id' => $org->org_type_user_id,
                'org_name' => optional($org->org)->org_name ?? 'Unknown',
                'events' => $events[$org->org_type_user_id] ?? [],
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }
    public function past_events()
    {
        $userId = Auth::id();

        $orgs = OrgMember::with('org:id,org_name')
            ->where('individual_type_user_id', $userId)
            ->get();

        $orgIds = $orgs->pluck('org_type_user_id');

        $events = Event::whereIn('user_id', $orgIds)
            ->where('date', '>=', now())
            ->orderBy('date')
            ->get()
            ->groupBy('user_id');

        $result = $orgs->map(function ($org) use ($events) {
            return [
                'org_id' => $org->org_type_user_id,
                'org_name' => optional($org->org)->org_name ?? 'Unknown',
                'events' => $events[$org->org_type_user_id] ?? [],
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }

    public function projects()
    {
        $userId = Auth::id();

        $orgs = OrgMember::with('org:id,org_name')
            ->where('individual_type_user_id', $userId)
            ->get();

        $orgIds = $orgs->pluck('org_type_user_id');

        $projects = Project::whereIn('user_id', $orgIds)
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->get()
            ->groupBy('user_id');

        $result = $orgs->map(function ($org) use ($projects) {
            return [
                'org_id' => $org->org_type_user_id,
                'org_name' => optional($org->org)->org_name ?? 'Unknown',
                'projects' => $projects[$org->org_type_user_id] ?? [],
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }

    public function past_projects()
    {
        $userId = Auth::id();

        $orgs = OrgMember::with('org:id,org_name')
            ->where('individual_type_user_id', $userId)
            ->get();

        $orgIds = $orgs->pluck('org_type_user_id');

        $projects = Project::whereIn('user_id', $orgIds)
            ->where('start_date', '<=', now())
            ->orderBy('start_date')
            ->get()
            ->groupBy('user_id');

        $result = $orgs->map(function ($org) use ($projects) {
            return [
                'org_id' => $org->org_type_user_id,
                'org_name' => optional($org->org)->org_name ?? 'Unknown',
                'projects' => $projects[$org->org_type_user_id] ?? [],
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $result
        ]);
    }

    public function assets()
    {
        $userId = Auth::id();

        // Get connected organisations
        $orgs = OrgMember::with('org:id,org_name')
            ->where('individual_type_user_id', $userId)
            ->get();

        $orgIds = $orgs->pluck('org_type_user_id');

        // Fetch all active assignment logs for the user with asset info
        $assignmentLogs = AssetAssignmentLog::with(['asset:id,name,quantity,user_id'])
            ->whereRaw('(assignment_end_date >= ? OR assignment_end_date IS NULL)', [now()])
            ->where('responsible_user_id', $userId)
            ->where('is_active', true)
            ->get();

        // Group logs by organisation ID (asset.user_id)
        $assetsByOrg = $assignmentLogs->groupBy(fn($log) => optional($log->asset)->user_id);

        // Map response per organisation
        $result = $orgs->map(function ($org) use ($assetsByOrg) {
            $orgId = $org->org_type_user_id;

            $assets = $assetsByOrg[$orgId] ?? collect();

            return [
                'org_id' => $orgId,
                'org_name' => optional($org->org)->org_name ?? 'Unknown',
                'assets' => $assets->map(function ($log) {
                    return [
                        'asset_id' => $log->asset_id,
                        'name' => optional($log->asset)->name,
                        'quantity' => optional($log->asset)->quantity,
                        'assignment_start_date' => $log->assignment_start_date,
                        'assignment_end_date' => $log->assignment_end_date,
                        'asset_lifecycle_status_id' => $log->asset_lifecycle_statuses_id,
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $result,
        ]);
    }

    public function past_assets()
    {
        $userId = Auth::id();

        // Get connected organisations
        $orgs = OrgMember::with('org:id,org_name')
            ->where('individual_type_user_id', $userId)
            ->get();

        $orgIds = $orgs->pluck('org_type_user_id');

        // Fetch all active assignment logs for the user with asset info
        $assignmentLogs = AssetAssignmentLog::with(['asset:id,name,quantity,user_id'])
            ->where('assignment_end_date', '<=', now())
            ->where('responsible_user_id', $userId)
            ->where('is_active', true)
            ->get();

        // Group logs by organisation ID (asset.user_id)
        $assetsByOrg = $assignmentLogs->groupBy(fn($log) => optional($log->asset)->user_id);

        // Map response per organisation
        $result = $orgs->map(function ($org) use ($assetsByOrg) {
            $orgId = $org->org_type_user_id;

            $assets = $assetsByOrg[$orgId] ?? collect();

            return [
                'org_id' => $orgId,
                'org_name' => optional($org->org)->org_name ?? 'Unknown',
                'assets' => $assets->map(function ($log) {
                    return [
                        'asset_id' => $log->asset_id,
                        'name' => optional($log->asset)->name,
                        'quantity' => optional($log->asset)->quantity,
                        'assignment_start_date' => $log->assignment_start_date,
                        'assignment_end_date' => $log->assignment_end_date,
                        'asset_lifecycle_status_id' => $log->asset_lifecycle_statuses_id,
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $result,
        ]);
    }

    protected function success($message, $data = [], $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function getProfileImage($userId)
    {
        $logo = ProfileImage::where('user_id', $userId)->orderBy('id', 'desc')->first();
        $imageUrl = $logo ? Storage::url($logo->image_path) : null;
        return response()->json([
            'status' => true,
            'data' => ['image' => $imageUrl]
        ]);
    }
    public function updateProfileImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20048',
        ]);
        $userId = $request->user()->id;
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Organization not found'], 404);
        }
        $image = $request->file('image');
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $mime_type = 'image' . '/' . $extension;
        $fileSize = $image->getSize();
        $timestamp = Carbon::now()->format('YmdHis');
        $newFileName = $timestamp . '_' . $originalName . '.' . $extension;
        $path = $image->storeAs('individual/profile/image', $newFileName, 'public');
        $orgLogo = ProfileImage::where('user_id', $userId)->orderBy('id', 'desc')->first();
        if ($orgLogo) {
            $orgLogo->user_id = $userId;
            $orgLogo->image_path = $path;
            $orgLogo->file_name = $originalName;
            $orgLogo->mime_type = $mime_type;
            $orgLogo->file_size = $fileSize;
            $orgLogo->save();
        } else {
            $orgLogo = new ProfileImage();
            $orgLogo->user_id = $userId;
            $orgLogo->image_path = $path;
            $orgLogo->file_name = $originalName;
            $orgLogo->mime_type = $mime_type;
            $orgLogo->file_size = $fileSize;
            $orgLogo->save();
        }
        $imageUrl = Storage::url($path);
        return response()->json(['status' => true, 'data' => ['image' => $imageUrl]]);
    }

    public function getOrganisationByIndividualId($individualId)
    {
        $organisations = OrgMember::where('individual_id', $individualId)
            ->with('connectedorg')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $organisations,
        ]);
    }
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show($id)
    {
        $individualData = Individual::where('user_id', $id)->first();
        if ($individualData) {
            return response()->json(['status' => true, 'data' => $individualData]);
        } else {
            return response()->json(['status' => false, 'message' => 'Individual data not found']);
        }
    }

    public function attendance()
    {
        $userId = Auth::id();
        $today = Carbon::today();

        $meetingsAttended = MeetingAttendance::where('user_id', $userId)->count();
        $eventsAttended = EventAttendance::where('user_id', $userId)->count();
        $projectsParticipated = ProjectAttendance::where('user_id', $userId)->count();

        // --- Attended Meetings ---
        $pastMeetings = Meeting::whereDate('date', '<=', $today)
            ->whereHas('meetingAttendances', fn($q) => $q->where('user_id', $userId))
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'type' => 'Meeting',
                'title' => $m->name,
                'date' => $m->date,
                'status' => 'attended',
            ]);

        // --- Absent Meetings ---
        $pastMeetingsAbsent = Meeting::whereDate('date', '<=', $today)
            ->whereDoesntHave('meetingAttendances', fn($q) => $q->where('user_id', $userId))
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'type' => 'Meeting',
                'title' => $m->name,
                'date' => $m->date,
                'status' => 'absent',
            ]);

        // --- Attended Events ---
        $pastEvents = Event::whereDate('date', '<=', $today)
            ->whereHas('eventAttendances', fn($q) => $q->where('user_id', $userId))
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'type' => 'Event',
                'title' => $e->title,
                'date' => $e->date,
                'status' => 'attended',
            ]);

        // --- Absent Events ---
        $pastEventsAbsent = Event::whereDate('date', '<=', $today)
            ->whereDoesntHave('eventAttendances', fn($q) => $q->where('user_id', $userId))
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'type' => 'Event',
                'title' => $e->title,
                'date' => $e->date,
                'status' => 'absent',
            ]);

        // --- Participated Projects ---
        $pastProjects = Project::whereDate('end_date', '<=', $today)
            ->whereHas('projectAttendances', fn($q) => $q->where('user_id', $userId))
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'type' => 'Project',
                'title' => $p->title,
                'date' => $p->end_date,
                'status' => 'attended',
            ]);

        // --- Absent Projects ---
        $pastProjectsAbsent = Project::whereDate('end_date', '<=', $today)
            ->whereDoesntHave('projectAttendances', fn($q) => $q->where('user_id', $userId))
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'type' => 'Project',
                'title' => $p->title,
                'date' => $p->end_date,
                'status' => 'absent',
            ]);

        return response()->json([
            'stats' => [
                'meetings_attended' => $meetingsAttended,
                'events_attended' => $eventsAttended,
                'projects_participated' => $projectsParticipated,
            ],
            'past' => $pastMeetings
                ->concat($pastEvents)
                ->concat($pastProjects)
                ->sortByDesc('date')
                ->values(),

            'past_absent' => $pastMeetingsAbsent
                ->concat($pastEventsAbsent)
                ->concat($pastProjectsAbsent)
                ->sortByDesc('date')
                ->values(),
        ]);
    }



    public function edit(Individual $individual) {}
    public function update(Request $request, Individual $individual) {}
    public function destroy(Individual $individual) {}
}
