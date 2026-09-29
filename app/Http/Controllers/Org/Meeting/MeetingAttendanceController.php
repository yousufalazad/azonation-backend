<?php
namespace App\Http\Controllers\Org\Meeting;

use App\Http\Concerns\ManagesAttendance;
use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use Illuminate\Routing\Controller;

/**
 * Members' attendance at a meeting: status (Present, Late, Absent...) and how they
 * attended (In Person, Online...). The logic is shared in ManagesAttendance.
 */
class MeetingAttendanceController extends Controller
{
    use ResolvesCurrentOrg, ManagesAttendance;

    public function __construct()
    {
        $this->middleware('org.permission:meeting-attendance.read')->only(['index', 'show']);
        $this->middleware('org.permission:meeting-attendance.create')->only(['create', 'store', 'bulkStore']);
        $this->middleware('org.permission:meeting-attendance.update')->only(['edit', 'update']);
        $this->middleware('org.permission:meeting-attendance.delete')->only(['destroy']);
    }

    protected function attendanceModel(): string { return MeetingAttendance::class; }
    protected function parentModel(): string { return Meeting::class; }
    protected function parentKey(): string { return 'meeting_id'; }
}
