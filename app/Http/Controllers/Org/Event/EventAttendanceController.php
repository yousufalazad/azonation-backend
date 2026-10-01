<?php
namespace App\Http\Controllers\Org\Event;

use App\Http\Concerns\ManagesAttendance;
use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\Event;
use App\Models\EventAttendance;
use Illuminate\Routing\Controller;

/**
 * Members' attendance at a event: status (Present, Late, Absent...) and how they
 * attended (In Person, Online...). The logic is shared in ManagesAttendance.
 */
class EventAttendanceController extends Controller
{
    use ResolvesCurrentOrg, ManagesAttendance;

    public function __construct()
    {
        $this->middleware('org.permission:event-attendance.read')->only(['index', 'show']);
        $this->middleware('org.permission:event-attendance.create')->only(['create', 'store', 'bulkStore']);
        $this->middleware('org.permission:event-attendance.update')->only(['edit', 'update']);
        $this->middleware('org.permission:event-attendance.delete')->only(['destroy']);
    }

    protected function attendanceModel(): string { return EventAttendance::class; }
    protected function parentModel(): string { return Event::class; }
    protected function parentKey(): string { return 'event_id'; }
}
