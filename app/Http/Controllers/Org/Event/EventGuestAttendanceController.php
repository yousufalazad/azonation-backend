<?php
namespace App\Http\Controllers\Org\Event;

use App\Http\Concerns\ManagesGuestAttendance;
use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\Event;
use App\Models\EventGuestAttendance;
use Illuminate\Routing\Controller;

/**
 * Guests (people who are not members) at a event. The logic is shared in ManagesGuestAttendance.
 */
class EventGuestAttendanceController extends Controller
{
    use ResolvesCurrentOrg, ManagesGuestAttendance;

    public function __construct()
    {
        $this->middleware('org.permission:event-guest-attendance.read')->only(['index', 'show']);
        $this->middleware('org.permission:event-guest-attendance.create')->only(['create', 'store']);
        $this->middleware('org.permission:event-guest-attendance.update')->only(['edit', 'update']);
        $this->middleware('org.permission:event-guest-attendance.delete')->only(['destroy']);
    }

    protected function guestModel(): string { return EventGuestAttendance::class; }
    protected function parentModel(): string { return Event::class; }
    protected function parentKey(): string { return 'event_id'; }
}
