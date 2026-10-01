<?php

namespace App\Http\Concerns;

use App\Models\AttendanceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/**
 * Members' attendance at something an organisation holds (a meeting, an event...).
 * Each record has:
 *  - attendance_status_id: what happened (Present, Late, Absent...), required
 *  - attendance_type_id:   how they took part (In Person, Online...), required when
 *                          the status means they attended, empty when they did not
 *
 * The controller using this trait also uses ResolvesCurrentOrg and defines:
 *   protected function attendanceModel(): string   e.g. MeetingAttendance::class
 *   protected function parentModel(): string       e.g. Meeting::class
 *   protected function parentKey(): string         e.g. 'meeting_id'
 */
trait ManagesAttendance
{
    use AttendanceRules;

    // ?<parentKey>=5 returns one meeting's / event's attendance only
    public function index(Request $request)
    {
        $model = $this->attendanceModel();
        $table = (new $model)->getTable();
        $key = $this->parentKey();

        $rows = $this->ownedVia($model, $key, $this->parentModel())
            ->select(
                "$table.*",
                'users.first_name as user_first_name',
                'users.last_name as user_last_name',
                DB::raw("CONCAT_WS(' ', users.first_name, users.last_name) as user_name"),
                'attendance_types.name as attendance_types_name',
                'attendance_statuses.name as attendance_status_name',
                'attendance_statuses.is_attended as is_attended'
            )
            ->leftJoin('users', "$table.user_id", '=', 'users.id')
            ->leftJoin('attendance_types', "$table.attendance_type_id", '=', 'attendance_types.id')
            ->leftJoin('attendance_statuses', "$table.attendance_status_id", '=', 'attendance_statuses.id')
            ->when($request->query($key), fn ($q, $parentId) => $q->where("$table.$key", $parentId))
            ->get();
        return response()->json(['status' => true, 'data' => $rows], 200);
    }

    public function create() {}
    public function show($id) {}
    public function edit($id) {}

    /**
     * Save many members at once: one record per member per meeting/event (insert or update)
     */
    public function bulkStore(Request $request)
    {
        $key = $this->parentKey();
        $parentTable = (new ($this->parentModel()))->getTable();

        $validator = Validator::make($request->all(), [
            "*.$key"                 => "required|exists:$parentTable,id",
            '*.user_id'              => 'required|exists:users,id',
            '*.attendance_status_id' => 'required|exists:attendance_statuses,id',
            '*.attendance_type_id'   => 'nullable|exists:attendance_types,id',
            '*.time'                 => 'nullable',
            '*.note'                 => 'nullable|string',
            '*.is_active'            => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        // Every meeting/event in the list must belong to this organisation
        foreach (collect($request->all())->pluck($key)->unique() as $parentId) {
            $this->ensureOwnedParent($this->parentModel(), $parentId);
        }

        $attended = $this->attendedMap();
        foreach ($request->all() as $i => $row) {
            if ($attended[$row['attendance_status_id']] && empty($row['attendance_type_id'])) {
                return $this->needTypeResponse("$i.attendance_type_id");
            }
        }

        $model = $this->attendanceModel();
        DB::beginTransaction();
        try {
            foreach ($request->all() as $row) {
                $didAttend = $attended[$row['attendance_status_id']];
                $model::updateOrCreate(
                    [$key => $row[$key], 'user_id' => $row['user_id']],
                    [
                        'attendance_status_id' => $row['attendance_status_id'],
                        // No "how" or arrival time for someone who was not there
                        'attendance_type_id'   => $didAttend ? $row['attendance_type_id'] : null,
                        'time'                 => $didAttend ? ($row['time'] ?? null) : null,
                        'note'                 => $row['note'] ?? null,
                        'is_active'            => $this->activeValue($row['is_active'] ?? true),
                    ]
                );
            }
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Attendance saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving attendance: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to save attendance'], 500);
        }
    }

    public function store(Request $request)
    {
        $this->ensureOwnedParent($this->parentModel(), $request->input($this->parentKey()));

        $validator = Validator::make($request->all(), $this->memberRules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $didAttend = $this->attendedMap()[$request->attendance_status_id];
        if ($didAttend && !$request->attendance_type_id) {
            return $this->needTypeResponse('attendance_type_id');
        }
        $model = $this->attendanceModel();
        $record = $model::create($this->memberValues($request, $didAttend));
        return response()->json(['status' => true, 'data' => $record, 'message' => 'Attendance saved.'], 201);
    }

    public function update(Request $request, $id)
    {
        $key = $this->parentKey();
        if ($request->has($key)) $this->ensureOwnedParent($this->parentModel(), $request->input($key));

        $validator = Validator::make($request->all(), $this->memberRules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $record = $this->ownedVia($this->attendanceModel(), $key, $this->parentModel())->find($id);
        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Attendance not found.'], 404);
        }
        $didAttend = $this->attendedMap()[$request->attendance_status_id];
        if ($didAttend && !$request->attendance_type_id) {
            return $this->needTypeResponse('attendance_type_id');
        }
        $record->update($this->memberValues($request, $didAttend));
        return response()->json(['status' => true, 'data' => $record, 'message' => 'Attendance saved.'], 200);
    }

    public function destroy($id)
    {
        $record = $this->ownedVia($this->attendanceModel(), $this->parentKey(), $this->parentModel())->find($id);
        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Attendance not found.'], 404);
        }
        $record->delete();
        return response()->json(['status' => true, 'message' => 'Attendance removed.'], 200);
    }

    private function memberRules(): array
    {
        $parentTable = (new ($this->parentModel()))->getTable();
        return [
            $this->parentKey() => "required|exists:$parentTable,id",
            'user_id' => 'required|exists:users,id',
            'attendance_status_id' => 'required|exists:attendance_statuses,id',
            'attendance_type_id' => 'nullable|exists:attendance_types,id',
            'date' => 'nullable|date',
            'time' => 'nullable',
            'note' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    private function memberValues(Request $request, bool $didAttend): array
    {
        $values = [
            $this->parentKey() => $request->input($this->parentKey()),
            'user_id' => $request->user_id,
            'attendance_status_id' => $request->attendance_status_id,
            'attendance_type_id' => $didAttend ? $request->attendance_type_id : null,
            'time' => $didAttend ? $request->time : null,
            'note' => $request->note,
            'is_active' => $this->activeValue($request->input('is_active', true)),
        ];
        // Some attendance tables also keep the date
        $model = $this->attendanceModel();
        if (Schema::hasColumn((new $model)->getTable(), 'date')) {
            $values['date'] = $request->date;
        }
        return $values;
    }
}
