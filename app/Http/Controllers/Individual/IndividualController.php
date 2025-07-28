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
        $connectedOrgs = OrgMember::where('individual_type_user_id', $userId)
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
        $assignmentLogs = \App\Models\AssetAssignmentLog::with(['asset:id,name,quantity,user_id'])
            ->where('responsible_user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->groupBy(fn($log) => optional($log->asset)->user_id); // Group by organisation ID

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

    public function events()
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
    public function edit(Individual $individual) {}
    public function update(Request $request, Individual $individual) {}
    public function destroy(Individual $individual) {}
}
