<?php
namespace App\Http\Controllers\Org\Project;

use App\Http\Concerns\ManagesGuestAttendance;
use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\Project;
use App\Models\ProjectGuestAttendance;
use Illuminate\Routing\Controller;

/**
 * Guests (people who are not members) at a project. The logic is shared in ManagesGuestAttendance.
 */
class ProjectGuestAttendanceController extends Controller
{
    use ResolvesCurrentOrg, ManagesGuestAttendance;

    public function __construct()
    {
        $this->middleware('org.permission:project-guest-attendance.read')->only(['index', 'show']);
        $this->middleware('org.permission:project-guest-attendance.create')->only(['create', 'store']);
        $this->middleware('org.permission:project-guest-attendance.update')->only(['edit', 'update']);
        $this->middleware('org.permission:project-guest-attendance.delete')->only(['destroy']);
    }

    protected function guestModel(): string { return ProjectGuestAttendance::class; }
    protected function parentModel(): string { return Project::class; }
    protected function parentKey(): string { return 'project_id'; }
}
