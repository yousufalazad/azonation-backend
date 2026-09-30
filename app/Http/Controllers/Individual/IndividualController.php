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
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class IndividualController extends Controller
{
    /**
     * The member's home page: for each organisation they currently belong to,
     * what is coming up (meetings, events, projects), their committees and the
     * assets they hold. A few queries in total, whatever the number of organisations.
     */
    public function summary()
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        $memberships = $this->memberships($userId)->where('is_active', true)->values();
        $orgIds = $memberships->pluck('org_id');

        $meetings = Meeting::whereIn('user_id', $orgIds)->where('is_active', 1)->whereDate('date', '>=', $today)
            ->orderBy('date')->orderBy('start_time')
            ->get(['id', 'user_id', 'name', 'date', 'start_time', 'venue', 'meeting_mode'])
            ->groupBy('user_id');
        $events = Event::whereIn('user_id', $orgIds)->whereDate('date', '>=', $today)
            ->orderBy('date')->orderBy('time')
            ->get(['id', 'user_id', 'title', 'date', 'time', 'venue_name'])
            ->groupBy('user_id');
        $projects = Project::whereIn('user_id', $orgIds)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today))
            ->orderBy('start_date')
            ->get(['id', 'user_id', 'title', 'start_date', 'end_date'])
            ->groupBy('user_id');

        $committees = CommitteeMember::query()
            ->join('committees', 'committees.id', '=', 'committee_members.committee_id')
            ->leftJoin('designations', 'designations.id', '=', 'committee_members.designation_id')
            ->where('committee_members.user_id', $userId)
            ->where('committee_members.is_active', 1)
            ->where(fn ($q) => $q->whereNull('committee_members.end_date')->orWhereDate('committee_members.end_date', '>=', $today))
            ->whereIn('committees.user_id', $orgIds)
            ->get(['committees.id', 'committees.user_id', 'committees.name', 'designations.name as designation'])
            ->groupBy('user_id');

        $assets = AssetAssignmentLog::with(['asset:id,name,quantity,user_id', 'lifecycle:id,name'])
            ->where('responsible_user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->groupBy(fn ($log) => optional($log->asset)->user_id);

        $organisations = $memberships->map(function ($m) use ($meetings, $events, $projects, $committees, $assets) {
            $orgId = $m['org_id'];
            return $m + [
                'next_meetings' => ($meetings[$orgId] ?? collect())->take(3)->values(),
                'upcoming_events' => ($events[$orgId] ?? collect())->take(3)->values(),
                'projects' => ($projects[$orgId] ?? collect())->take(3)->values(),
                'committees' => ($committees[$orgId] ?? collect())->map->only(['id', 'name', 'designation'])->values(),
                'assets' => ($assets[$orgId] ?? collect())->map(fn ($log) => [
                    'id' => $log->asset_id,
                    'name' => optional($log->asset)->name,
                    'quantity' => optional($log->asset)->quantity,
                    'since' => $log->assignment_start_date,
                    'condition' => optional($log->lifecycle)->name,
                ])->values(),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => [
                'azon_id' => Auth::user()->azon_id,
                'organisations' => $organisations,
            ],
        ]);
    }

    // Every organisation the member belongs or belonged to, with their membership details
    public function getOrganisationByIndividualId()
    {
        return response()->json([
            'status' => true,
            'data' => $this->memberships(Auth::id())->values(),
        ]);
    }

    private function memberships(int $userId)
    {
        $rows = OrgMember::query()
            ->where('org_members.individual_type_user_id', $userId)
            ->join('users as org', 'org.id', '=', 'org_members.org_type_user_id')
            ->leftJoin('membership_types', 'membership_types.id', '=', 'org_members.membership_type_id')
            ->leftJoin('membership_statuses', 'membership_statuses.id', '=', 'org_members.membership_status_id')
            ->orderByDesc('org_members.is_active')
            ->orderBy('org.org_name')
            ->get([
                'org_members.org_type_user_id as org_id',
                'org.org_name',
                'org_members.existing_membership_id as membership_id',
                'org_members.membership_start_date',
                'org_members.is_active',
                'membership_types.name as membership_type',
                'membership_statuses.name as membership_status',
            ]);

        $logos = ProfileImage::whereIn('user_id', $rows->pluck('org_id'))->orderBy('id')->pluck('image_path', 'user_id');
        // Latest completed membership fee payment in each organisation: paid until when
        $paidUntil = DB::table('org_membership_renewals')
            ->where('individual_type_user_id', $userId)->where('status', 'completed')
            ->whereIn('org_type_user_id', $rows->pluck('org_id'))
            ->groupBy('org_type_user_id')
            ->pluck(DB::raw('MAX(period_end)'), 'org_type_user_id');

        return $rows->map(fn ($r) => [
            'org_id' => (int) $r->org_id,
            'org_name' => $r->org_name,
            'logo_url' => isset($logos[$r->org_id]) ? url(Storage::url($logos[$r->org_id])) : null,
            'membership_id' => $r->membership_id,
            'membership_type' => $r->membership_type,
            'membership_status' => $r->membership_status,
            'member_since' => $r->membership_start_date ? Carbon::parse($r->membership_start_date)->toDateString() : null,
            'is_active' => (bool) $r->is_active,
            'paid_until' => isset($paidUntil[$r->org_id]) ? Carbon::parse($paidUntil[$r->org_id])->toDateString() : null,
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
        $imageUrl = $logo ? url(Storage::url($logo->image_path)) : null;
        return response()->json([
            'status' => true,
            'data' => ['image' => $imageUrl]
        ]);
    }
    public function updateProfileImage(Request $request)
    {
        $request->validate([
            // No SVG: it can carry scripts
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
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
        $imageUrl = url(Storage::url($path));
        return response()->json(['status' => true, 'data' => ['image' => $imageUrl]]);
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
