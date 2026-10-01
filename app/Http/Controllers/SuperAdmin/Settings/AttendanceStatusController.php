<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\AttendanceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * What happened with a person's attendance: Present, Late, Absent, Excused...
 * Everyone signed in can read the list; only Super Admins change it
 * (see the guard loop at the end of routes/api.php).
 */
class AttendanceStatusController extends Controller
{
    public function index(Request $request)
    {
        $statuses = AttendanceStatus::query()
            // ?all=1 includes inactive ones (for the Super Admin settings page)
            ->when(!$request->boolean('all'), fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')->orderBy('id')
            ->get();
        return response()->json(['status' => true, 'data' => $statuses], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $status = AttendanceStatus::create($validator->validated());
        return response()->json(['status' => true, 'data' => $status, 'message' => 'Attendance status created.'], 201);
    }

    public function update(Request $request, $id)
    {
        $status = AttendanceStatus::find($id);
        if (!$status) {
            return response()->json(['status' => false, 'message' => 'Attendance status not found.'], 404);
        }
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $status->update($validator->validated());
        return response()->json(['status' => true, 'data' => $status, 'message' => 'Attendance status updated.'], 200);
    }

    public function destroy($id)
    {
        $status = AttendanceStatus::find($id);
        if (!$status) {
            return response()->json(['status' => false, 'message' => 'Attendance status not found.'], 404);
        }
        // Records using it keep working: their status becomes empty (nullOnDelete)
        $status->delete();
        return response()->json(['status' => true, 'message' => 'Attendance status deleted.'], 200);
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'is_attended' => 'required|boolean',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'required|boolean',
        ];
    }
}
