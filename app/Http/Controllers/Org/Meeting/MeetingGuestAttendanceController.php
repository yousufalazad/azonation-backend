<?php
namespace App\Http\Controllers\Org\Meeting;

use App\Http\Concerns\ManagesGuestAttendance;
use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\Meeting;
use App\Models\MeetingGuestAttendance;
use Illuminate\Routing\Controller;

/**
 * Guests (people who are not members) at a meeting. The logic is shared in ManagesGuestAttendance.
 */
class MeetingGuestAttendanceController extends Controller
{
    use ResolvesCurrentOrg, ManagesGuestAttendance;

    public function __construct()
    {
        $this->middleware('org.permission:meeting-guest-attendance.read')->only(['index', 'show']);
        $this->middleware('org.permission:meeting-guest-attendance.create')->only(['create', 'store']);
        $this->middleware('org.permission:meeting-guest-attendance.update')->only(['edit', 'update']);
        $this->middleware('org.permission:meeting-guest-attendance.delete')->only(['destroy']);
    }

    protected function guestModel(): string { return MeetingGuestAttendance::class; }
    protected function parentModel(): string { return Meeting::class; }
    protected function parentKey(): string { return 'meeting_id'; }
}
