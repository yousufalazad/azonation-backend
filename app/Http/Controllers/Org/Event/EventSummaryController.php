<?php
namespace App\Http\Controllers\Org\Event;

use Illuminate\Routing\Controller;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\EventGuestAttendance;
use App\Models\EventSummary;
use App\Models\EventSummaryFile;
use App\Models\EventSummaryImage;
use App\Http\Concerns\ResolvesCurrentOrg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * The report written after an event: how it went, highlights, money, next steps.
 * Attendance totals default to the count from the event's attendance records.
 */
class EventSummaryController extends Controller
{
    use ResolvesCurrentOrg;

    private const TEXT_FIELDS = [
        'summary', 'highlights', 'feedback', 'challenges', 'suggestions', 'financial_overview', 'next_steps',
    ];

    public function __construct()
    {
        $this->middleware('org.permission:event-summary.read')->only(['index', 'show']);
        $this->middleware('org.permission:event-summary.create')->only(['create', 'store']);
        $this->middleware('org.permission:event-summary.update')->only(['edit', 'update']);
        $this->middleware('org.permission:event-summary.delete')->only(['destroy']);
    }

    public function index()
    {
        $summaries = $this->ownedVia(EventSummary::class, 'event_id', Event::class)
            ->select('event_summaries.*', 'events.name as event_name', 'events.date as event_date')
            ->leftJoin('events', 'event_summaries.event_id', '=', 'events.id')
            ->get();
        return response()->json(['status' => true, 'data' => $summaries], 200);
    }

    public function show($id)
    {
        $eventSummary = $this->ownedVia(EventSummary::class, 'event_id', Event::class)
            ->select(
                'event_summaries.*',
                'privacy_setups.id as privacy_id',
                'privacy_setups.name as privacy_setup_name',
                'events.name as event_name',
                'events.date as event_date',
                'events.time as event_time',
                'events.venue_name as event_venue'
            )
            ->leftJoin('privacy_setups', 'event_summaries.privacy_setup_id', '=', 'privacy_setups.id')
            ->leftJoin('events', 'event_summaries.event_id', '=', 'events.id')
            ->with(['images', 'documents'])
            ->where('event_summaries.id', $id)->first();
        if (!$eventSummary) {
            return response()->json(['status' => false, 'message' => 'Event Summary not found'], 404);
        }
        $eventSummary->images = $eventSummary->images->map(function ($image) {
            $image->image_url = $image->file_path ? url(Storage::url($image->file_path)) : null;
            return $image;
        });
        $eventSummary->documents = $eventSummary->documents->map(function ($document) {
            $document->document_url = $document->file_path ? url(Storage::url($document->file_path)) : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $eventSummary], 200);
    }

    public function store(Request $request)
    {
        $this->ensureOwnedParent(Event::class, $request->input('event_id'));

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        try {
            $eventSummary = new EventSummary();
            $eventSummary->event_id = $request->event_id;
            $eventSummary->created_by = $request->user()->id;
            $this->fill($eventSummary, $request);
            $eventSummary->save();
            $this->saveFiles($request, $eventSummary);
            return response()->json(['status' => true, 'data' => $eventSummary, 'message' => 'Event summary created successfully!'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Event Summary: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if ($request->has('event_id')) $this->ensureOwnedParent(Event::class, $request->input('event_id'));

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $eventSummary = $this->ownedVia(EventSummary::class, 'event_id', Event::class)->find($id);
        if (!$eventSummary) {
            return response()->json(['status' => false, 'message' => 'Event Summary not found'], 404);
        }
        try {
            $eventSummary->event_id = $request->event_id;
            $this->fill($eventSummary, $request);
            $eventSummary->save();
            $this->saveFiles($request, $eventSummary);
            return response()->json(['status' => true, 'data' => $eventSummary, 'message' => 'Event summary updated successfully!'], 200);
        } catch (\Exception $e) {
            Log::error('Error updating Event Summary: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    public function destroy($id)
    {
        $eventSummary = $this->ownedVia(EventSummary::class, 'event_id', Event::class)->with(['images', 'documents'])->find($id);
        if (!$eventSummary) {
            return response()->json(['status' => false, 'message' => 'Event Summary not found'], 404);
        }
        $paths = collect([$eventSummary->image_attachment, $eventSummary->file_attachment])
            ->merge($eventSummary->images->pluck('file_path'))
            ->merge($eventSummary->documents->pluck('file_path'))
            ->filter();
        $eventSummary->images()->delete();
        $eventSummary->documents()->delete();
        $eventSummary->delete();
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
        return response()->json(['status' => true, 'message' => 'Event summary deleted successfully!'], 200);
    }

    private function rules(): array
    {
        return [
            'event_id' => 'required|integer|exists:events,id',
            'privacy_setup_id' => 'required|integer|exists:privacy_setups,id',
            'summary' => 'nullable|string',
            'highlights' => 'nullable|string',
            'feedback' => 'nullable|string',
            'challenges' => 'nullable|string',
            'suggestions' => 'nullable|string',
            'financial_overview' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'total_member_attendance' => 'nullable|integer|min:0',
            'total_guest_attendance' => 'nullable|integer|min:0',
            'total_expense' => 'nullable|integer|min:0',
            'is_publish' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ];
    }

    private function fill(EventSummary $eventSummary, Request $request): void
    {
        foreach (self::TEXT_FIELDS as $field) {
            $eventSummary->{$field} = $request->input($field);
        }
        // Totals: what the person entered, otherwise the count of people marked as attended
        [$members, $guests] = $this->attendanceTotals((int) $request->event_id);
        $eventSummary->total_member_attendance = (int) ($request->input('total_member_attendance') ?? $members);
        $eventSummary->total_guest_attendance = (int) ($request->input('total_guest_attendance') ?? $guests);
        $eventSummary->total_expense = (int) ($request->input('total_expense') ?? 0);
        $eventSummary->privacy_setup_id = $request->privacy_setup_id;
        $eventSummary->is_publish = $request->boolean('is_publish');
        $eventSummary->is_active = $request->has('is_active') ? $request->boolean('is_active') : true;
        $eventSummary->updated_by = $request->user()->id;
    }

    private function attendanceTotals(int $eventId): array
    {
        $count = fn (string $model, string $table) => $model::query()
            ->join('attendance_statuses', "$table.attendance_status_id", '=', 'attendance_statuses.id')
            ->where("$table.event_id", $eventId)
            ->where('attendance_statuses.is_attended', true)
            ->count();
        return [
            $count(EventAttendance::class, 'event_attendances'),
            $count(EventGuestAttendance::class, 'event_guest_attendances'),
        ];
    }

    private function saveFiles(Request $request, EventSummary $eventSummary): void
    {
        foreach ((array) $request->file('documents', []) as $document) {
            $path = $document->storeAs('org/event-summary/file', Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(), 'public');
            EventSummaryFile::create([
                'event_summary_id' => $eventSummary->id,
                'file_path' => $path,
                'file_name' => $document->getClientOriginalName(),
                'mime_type' => $document->getClientMimeType(),
                'file_size' => $document->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);
        }
        foreach ((array) $request->file('images', []) as $image) {
            $path = $image->storeAs('org/event-summary/image', Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(), 'public');
            EventSummaryImage::create([
                'event_summary_id' => $eventSummary->id,
                'file_path' => $path,
                'file_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getClientMimeType(),
                'file_size' => $image->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);
        }
    }
}
