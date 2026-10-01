<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Models\AssetAssignmentLog;
use App\Models\CommitteeMember;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\OrgMember;
use App\Models\ProfileImage;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * What a member sees of their organisations: meetings, events, projects,
 * committees, items they hold and their own attendance.
 * Only organisations they currently belong to; a record from any other
 * organisation is "not found".
 */
class MemberActivityController extends Controller
{
    private const PAST_LIMIT = 200;

    // ---- Meetings ----

    public function meetings(Request $request)
    {
        $orgs = $this->orgs();
        $past = $request->query('when') === 'past';
        $today = Carbon::today()->toDateString();

        $rows = Meeting::whereIn('user_id', $orgs->keys())->where('is_active', 1)
            ->whereDate('date', $past ? '<' : '>=', $today)
            ->orderBy('date', $past ? 'desc' : 'asc')->orderBy('start_time', $past ? 'desc' : 'asc')
            ->limit($past ? self::PAST_LIMIT : 500)
            ->get(['id', 'user_id', 'name', 'date', 'start_time', 'end_time', 'venue', 'meeting_mode']);

        $mine = $this->myAttendance('meeting_attendances', 'meeting_id', $rows->pluck('id'));

        return $this->list($rows->map(fn ($m) => $this->withOrg($m->toArray(), $orgs) + ['my_attendance' => $mine[$m->id] ?? null]));
    }

    public function meeting($id)
    {
        $orgs = $this->orgs();
        $m = Meeting::whereIn('user_id', $orgs->keys())->where('is_active', 1)->findOrFail($id);

        $minutes = DB::table('meeting_minutes')->where('meeting_id', $m->id)->where('is_publish', 1)->where('is_active', 1)
            ->orderByDesc('id')->first(['minutes', 'decisions', 'action_items']);

        return response()->json(['status' => true, 'data' => $this->withOrg([
            'id' => $m->id,
            'user_id' => $m->user_id,
            'name' => $m->name,
            'subject' => $m->subject,
            'date' => $m->date,
            'start_time' => $m->start_time,
            'end_time' => $m->end_time,
            'meeting_mode' => $m->meeting_mode,
            'venue' => $m->venue,
            'video_conference_link' => $m->video_conference_link,
            'agenda' => $m->agenda,
            'description' => $m->description,
            'requirements' => $m->requirements,
            'minutes' => $minutes,
            'my_attendance' => $this->myAttendance('meeting_attendances', 'meeting_id', collect([$m->id]))[$m->id] ?? null,
        ], $orgs)]);
    }

    // ---- Events ----

    public function events(Request $request)
    {
        $orgs = $this->orgs();
        $past = $request->query('when') === 'past';
        $today = Carbon::today()->toDateString();

        $rows = Event::whereIn('user_id', $orgs->keys())
            ->whereDate('date', $past ? '<' : '>=', $today)
            ->orderBy('date', $past ? 'desc' : 'asc')->orderBy('time', $past ? 'desc' : 'asc')
            ->limit($past ? self::PAST_LIMIT : 500)
            ->get(['id', 'user_id', 'title', 'date', 'time', 'venue_name', 'venue_address', 'family_welcome']);

        $mine = $this->myAttendance('event_attendances', 'event_id', $rows->pluck('id'));

        return $this->list($rows->map(fn ($e) => $this->withOrg($e->toArray(), $orgs) + ['my_attendance' => $mine[$e->id] ?? null]));
    }

    public function event($id)
    {
        $orgs = $this->orgs();
        $e = Event::whereIn('user_id', $orgs->keys())->findOrFail($id);

        return response()->json(['status' => true, 'data' => $this->withOrg([
            'id' => $e->id,
            'user_id' => $e->user_id,
            'title' => $e->title,
            'short_description' => $e->short_description,
            'description' => $e->description,
            'date' => $e->date,
            'time' => $e->time,
            'venue_name' => $e->venue_name,
            'venue_address' => $e->venue_address,
            'requirements' => $e->requirements,
            'family_welcome' => (bool) $e->family_welcome,
            // So the page can suggest sharing family numbers when families are welcome
            'my_family_sharing' => \App\Models\MemberFamilyShare::where('user_id', Auth::id())->where('org_id', $e->user_id)->value('level') ?? 'none',
            'my_attendance' => $this->myAttendance('event_attendances', 'event_id', collect([$e->id]))[$e->id] ?? null,
        ], $orgs)]);
    }

    // ---- Projects (current = not finished yet) ----

    public function projects(Request $request)
    {
        $orgs = $this->orgs();
        $past = $request->query('when') === 'past';
        $today = Carbon::today()->toDateString();

        $rows = Project::whereIn('user_id', $orgs->keys())
            ->when($past,
                fn ($q) => $q->whereNotNull('end_date')->whereDate('end_date', '<', $today)->orderByDesc('end_date'),
                fn ($q) => $q->where(fn ($w) => $w->whereNull('end_date')->orWhereDate('end_date', '>=', $today))->orderBy('start_date'))
            ->limit($past ? self::PAST_LIMIT : 500)
            ->get(['id', 'user_id', 'title', 'short_description', 'start_date', 'end_date', 'venue_name']);

        $mine = $this->myAttendance('project_attendances', 'project_id', $rows->pluck('id'));

        return $this->list($rows->map(fn ($p) => $this->withOrg($p->toArray(), $orgs) + ['my_attendance' => $mine[$p->id] ?? null]));
    }

    public function project($id)
    {
        $orgs = $this->orgs();
        $p = Project::whereIn('user_id', $orgs->keys())->findOrFail($id);

        return response()->json(['status' => true, 'data' => $this->withOrg([
            'id' => $p->id,
            'user_id' => $p->user_id,
            'title' => $p->title,
            'short_description' => $p->short_description,
            'description' => $p->description,
            'start_date' => $p->start_date,
            'end_date' => $p->end_date,
            'start_time' => $p->start_time,
            'end_time' => $p->end_time,
            'venue_name' => $p->venue_name,
            'venue_address' => $p->venue_address,
            'requirements' => $p->requirements,
            'my_attendance' => $this->myAttendance('project_attendances', 'project_id', collect([$p->id]))[$p->id] ?? null,
        ], $orgs)]);
    }

    // ---- Committees the member is (or was) on ----

    public function committees(Request $request)
    {
        $orgs = $this->orgs();
        $past = $request->query('when') === 'past';
        $today = Carbon::today()->toDateString();

        $rows = CommitteeMember::query()
            ->join('committees', 'committees.id', '=', 'committee_members.committee_id')
            ->leftJoin('designations', 'designations.id', '=', 'committee_members.designation_id')
            ->where('committee_members.user_id', Auth::id())
            ->whereIn('committees.user_id', $orgs->keys())
            ->when($past,
                fn ($q) => $q->where(fn ($w) => $w->where('committee_members.is_active', 0)->orWhereDate('committee_members.end_date', '<', $today)),
                fn ($q) => $q->where('committee_members.is_active', 1)->where(fn ($w) => $w->whereNull('committee_members.end_date')->orWhereDate('committee_members.end_date', '>=', $today)))
            ->orderByDesc('committee_members.start_date')
            ->get([
                'committees.id', 'committees.user_id', 'committees.name', 'committees.short_description',
                'designations.name as designation', 'committee_members.start_date', 'committee_members.end_date',
            ]);

        return $this->list($rows->map(fn ($c) => $this->withOrg($c->toArray(), $orgs)));
    }

    // ---- Items the member looks after (or did) ----

    public function assets(Request $request)
    {
        $orgs = $this->orgs();
        $past = $request->query('when') === 'past';

        $rows = AssetAssignmentLog::query()
            ->join('assets', 'assets.id', '=', 'asset_assignment_logs.asset_id')
            ->leftJoin('asset_lifecycle_statuses', 'asset_lifecycle_statuses.id', '=', 'asset_assignment_logs.asset_lifecycle_statuses_id')
            ->where('asset_assignment_logs.responsible_user_id', Auth::id())
            ->whereIn('assets.user_id', $orgs->keys())
            ->where('asset_assignment_logs.is_active', $past ? 0 : 1)
            ->orderByDesc('asset_assignment_logs.assignment_start_date')
            ->limit(self::PAST_LIMIT)
            ->get([
                'assets.id', 'assets.user_id', 'assets.name', 'assets.quantity',
                'asset_assignment_logs.assignment_start_date as since', 'asset_assignment_logs.assignment_end_date as until',
                'asset_lifecycle_statuses.name as condition', 'asset_assignment_logs.note',
            ]);

        return $this->list($rows->map(fn ($a) => $this->withOrg($a->toArray(), $orgs)));
    }

    // ---- The member's own attendance record ----

    public function attendance()
    {
        $orgs = $this->orgs();
        $userId = Auth::id();
        $orgIds = $orgs->keys();

        $meetings = DB::table('meeting_attendances as a')
            ->join('meetings as x', 'x.id', '=', 'a.meeting_id')
            ->leftJoin('attendance_statuses as s', 's.id', '=', 'a.attendance_status_id')
            ->leftJoin('attendance_types as ty', 'ty.id', '=', 'a.attendance_type_id')
            ->where('a.user_id', $userId)->whereNull('a.deleted_at')->whereIn('x.user_id', $orgIds)
            ->get(['x.id', 'x.user_id', 'x.name as title', 'x.date', 's.name as status', 's.is_attended', 'ty.name as how', DB::raw("'meeting' as kind")]);
        $events = DB::table('event_attendances as a')
            ->join('events as x', 'x.id', '=', 'a.event_id')
            ->leftJoin('attendance_statuses as s', 's.id', '=', 'a.attendance_status_id')
            ->leftJoin('attendance_types as ty', 'ty.id', '=', 'a.attendance_type_id')
            ->where('a.user_id', $userId)->whereNull('a.deleted_at')->whereIn('x.user_id', $orgIds)
            ->get(['x.id', 'x.user_id', 'x.title', 'x.date', 's.name as status', 's.is_attended', 'ty.name as how', DB::raw("'event' as kind")]);
        $projects = DB::table('project_attendances as a')
            ->join('projects as x', 'x.id', '=', 'a.project_id')
            ->leftJoin('attendance_statuses as s', 's.id', '=', 'a.attendance_status_id')
            ->leftJoin('attendance_types as ty', 'ty.id', '=', 'a.attendance_type_id')
            ->where('a.user_id', $userId)->whereNull('a.deleted_at')->whereIn('x.user_id', $orgIds)
            ->get(['x.id', 'x.user_id', 'x.title', DB::raw('COALESCE(x.start_date, x.end_date) as date'), 's.name as status', 's.is_attended', 'ty.name as how', DB::raw("'project' as kind")]);

        $all = $meetings->concat($events)->concat($projects)
            ->map(function ($r) use ($orgs) {
                $row = (array) $r;
                // Older records have no status: being on the list meant they were there
                $row['attended'] = $row['status'] === null ? true : (bool) $row['is_attended'];
                unset($row['is_attended']);
                return $this->withOrg($row, $orgs);
            })
            ->sortByDesc('date')->values();

        $count = fn ($kind) => $all->where('kind', $kind)->where('attended', true)->count();

        return response()->json([
            'status' => true,
            'data' => [
                'stats' => ['meetings' => $count('meeting'), 'events' => $count('event'), 'projects' => $count('project')],
                'records' => $all,
            ],
        ]);
    }

    // ---- Helpers ----

    // Organisations the member currently belongs to: id => [name, logo]
    private function orgs(): Collection
    {
        $rows = OrgMember::query()
            ->where('org_members.individual_type_user_id', Auth::id())
            ->where('org_members.is_active', 1)
            ->join('users as org', 'org.id', '=', 'org_members.org_type_user_id')
            ->get(['org_members.org_type_user_id as id', 'org.org_name']);
        $logos = ProfileImage::whereIn('user_id', $rows->pluck('id'))->orderBy('id')->pluck('image_path', 'user_id');

        return $rows->mapWithKeys(fn ($r) => [(int) $r->id => [
            'org_name' => $r->org_name,
            'org_logo' => isset($logos[$r->id]) ? url(Storage::url($logos[$r->id])) : null,
        ]]);
    }

    private function withOrg(array $row, Collection $orgs): array
    {
        $org = $orgs[(int) $row['user_id']] ?? ['org_name' => null, 'org_logo' => null];
        $row['org_id'] = (int) $row['user_id'];
        unset($row['user_id']);
        return $row + $org;
    }

    // The member's own attendance on the given records: id => { status, attended, how }
    private function myAttendance(string $table, string $key, Collection $ids): array
    {
        if ($ids->isEmpty()) {
            return [];
        }
        return DB::table("$table as a")
            ->leftJoin('attendance_statuses as s', 's.id', '=', 'a.attendance_status_id')
            ->leftJoin('attendance_types as ty', 'ty.id', '=', 'a.attendance_type_id')
            ->where('a.user_id', Auth::id())->whereNull('a.deleted_at')->whereIn("a.$key", $ids)
            ->get(["a.$key as id", 's.name as status', 's.is_attended', 'ty.name as how'])
            ->mapWithKeys(fn ($r) => [$r->id => [
                'status' => $r->status,
                'attended' => $r->status === null ? true : (bool) $r->is_attended,
                'how' => $r->how,
            ]])->all();
    }

    private function list(Collection $rows)
    {
        return response()->json(['status' => true, 'data' => $rows->values()]);
    }
}
