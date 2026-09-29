<?php
namespace App\Http\Controllers\Org\Project;

use App\Http\Concerns\ManagesAttendance;
use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\Project;
use App\Models\ProjectAttendance;
use Illuminate\Routing\Controller;

/**
 * Members' attendance at a project: status (Present, Late, Absent...) and how they
 * attended (In Person, Online...). The logic is shared in ManagesAttendance.
 */
class ProjectAttendanceController extends Controller
{
    use ResolvesCurrentOrg, ManagesAttendance;

    public function __construct()
    {
        $this->middleware('org.permission:project-attendance.read')->only(['index', 'show']);
        $this->middleware('org.permission:project-attendance.create')->only(['create', 'store', 'bulkStore']);
        $this->middleware('org.permission:project-attendance.update')->only(['edit', 'update']);
        $this->middleware('org.permission:project-attendance.delete')->only(['destroy']);
    }

    protected function attendanceModel(): string { return ProjectAttendance::class; }
    protected function parentModel(): string { return Project::class; }
    protected function parentKey(): string { return 'project_id'; }
}
