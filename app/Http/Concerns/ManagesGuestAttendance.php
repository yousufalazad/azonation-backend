<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Guests (people who are not members) at a meeting, event...
 * Same status/type rules as ManagesAttendance.
 *
 * The controller using this trait also uses ResolvesCurrentOrg and defines:
 *   protected function guestModel(): string    e.g. MeetingGuestAttendance::class
 *   protected function parentModel(): string   e.g. Meeting::class
 *   protected function parentKey(): string     e.g. 'meeting_id'
 */
trait ManagesGuestAttendance
{
    use AttendanceRules;

    // ?<parentKey>=5 returns one meeting's / event's guests only
    public function index(Request $request)
    {
        $model = $this->guestModel();
        $table = (new $model)->getTable();
        $key = $this->parentKey();

        $guests = $this->ownedVia($model, $key, $this->parentModel())
            ->select(
                "$table.*",
                'attendance_types.name as attendance_types_name',
                'attendance_statuses.name as attendance_status_name',
                'attendance_statuses.is_attended as is_attended'
            )
            ->leftJoin('attendance_types', "$table.attendance_type_id", '=', 'attendance_types.id')
            ->leftJoin('attendance_statuses', "$table.attendance_status_id", '=', 'attendance_statuses.id')
            ->when($request->query($key), fn ($q, $parentId) => $q->where("$table.$key", $parentId))
            ->get();
        return response()->json(['status' => true, 'data' => $guests], 200);
    }

    public function create() {}
    public function show($id) {}
    public function edit($id) {}

    public function store(Request $request)
    {
        $this->ensureOwnedParent($this->parentModel(), $request->input($this->parentKey()));

        $validator = Validator::make($request->all(), $this->guestRules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $didAttend = $this->attendedMap()[$request->attendance_status_id];
        if ($didAttend && !$request->attendance_type_id) {
            return $this->needTypeResponse('attendance_type_id');
        }
        try {
            $model = $this->guestModel();
            $guest = $model::create($this->guestValues($request, $didAttend));
            return response()->json(['status' => true, 'data' => $guest, 'message' => 'Guest added.'], 201);
        } catch (\Exception $e) {
            Log::error('Error adding guest: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to add the guest.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $key = $this->parentKey();
        if ($request->has($key)) $this->ensureOwnedParent($this->parentModel(), $request->input($key));

        $validator = Validator::make($request->all(), $this->guestRules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $guest = $this->ownedVia($this->guestModel(), $key, $this->parentModel())->find($id);
        if (!$guest) {
            return response()->json(['status' => false, 'message' => 'Guest not found.'], 404);
        }
        $didAttend = $this->attendedMap()[$request->attendance_status_id];
        if ($didAttend && !$request->attendance_type_id) {
            return $this->needTypeResponse('attendance_type_id');
        }
        $guest->update($this->guestValues($request, $didAttend));
        return response()->json(['status' => true, 'data' => $guest, 'message' => 'Guest saved.'], 200);
    }

    public function destroy($id)
    {
        $guest = $this->ownedVia($this->guestModel(), $this->parentKey(), $this->parentModel())->find($id);
        if (!$guest) {
            return response()->json(['status' => false, 'message' => 'Guest not found.'], 404);
        }
        $guest->delete();
        return response()->json(['status' => true, 'message' => 'Guest removed.'], 200);
    }

    private function guestRules(): array
    {
        $parentTable = (new ($this->parentModel()))->getTable();
        return [
            $this->parentKey() => "required|exists:$parentTable,id",
            'guest_name' => 'required|string|max:255',
            'about_guest' => 'nullable|string',
            'attendance_status_id' => 'required|exists:attendance_statuses,id',
            'attendance_type_id' => 'nullable|exists:attendance_types,id',
            'date' => 'nullable|date',
            'time' => 'nullable',
            'note' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    private function guestValues(Request $request, bool $didAttend): array
    {
        return [
            $this->parentKey() => $request->input($this->parentKey()),
            'guest_name' => $request->guest_name,
            'about_guest' => $request->about_guest,
            'attendance_status_id' => $request->attendance_status_id,
            'attendance_type_id' => $didAttend ? $request->attendance_type_id : null,
            'date' => $request->date,
            'time' => $didAttend ? $request->time : null,
            'note' => $request->note,
            'is_active' => $this->activeValue($request->input('is_active', true)),
        ];
    }
}
