<?php
namespace App\Http\Controllers\Org\Meeting;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\Meeting;
use Illuminate\Routing\Controller;

use App\Models\MeetingMinutes;
use App\Models\MeetingMinuteFile;
use App\Models\MeetingMinuteImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MeetingMinutesController extends Controller
{
    use ResolvesCurrentOrg;

    // Text fields the form may send; everything else is set by the server
    private const TEXT_FIELDS = [
        'minutes', 'decisions', 'note', 'start_time', 'end_time', 'follow_up_tasks',
        'tags', 'action_items', 'meeting_location', 'video_link',
    ];

    public function __construct()
    {
        $this->middleware('org.permission:meeting-minute.read')->only(['index', 'show']);
        $this->middleware('org.permission:meeting-minute.create')->only(['create', 'store']);
        $this->middleware('org.permission:meeting-minute.update')->only(['edit', 'update']);
        $this->middleware('org.permission:meeting-minute.delete')->only(['destroy']);
    }

    public function index()
    {
        // Each row carries its meeting's name and date so lists need no second request
        $minutes = $this->ownedVia(MeetingMinutes::class, 'meeting_id', Meeting::class)
            ->select('meeting_minutes.*', 'meetings.name as meeting_name', 'meetings.date as meeting_date')
            ->leftJoin('meetings', 'meeting_minutes.meeting_id', '=', 'meetings.id')
            ->get();
        return response()->json(['status' => true, 'data' => $minutes], 200);
    }

    public function create() {}

    public function show($id)
    {
        $meetingMinute = $this->ownedVia(MeetingMinutes::class, 'meeting_id', Meeting::class)
            ->select(
                'meeting_minutes.*',
                'privacy_setups.id as privacy_id',
                'privacy_setups.name as privacy_setup_name',
                'meetings.name as meeting_name',
                'meetings.date as meeting_date',
                'meetings.start_time as meeting_start_time',
                'meetings.venue as meeting_venue',
                'preparer.first_name as prepared_by_first_name',
                'preparer.last_name as prepared_by_last_name',
                'reviewer.first_name as reviewed_by_first_name',
                'reviewer.last_name as reviewed_by_last_name'
            )
            ->leftJoin('privacy_setups', 'meeting_minutes.privacy_setup_id', '=', 'privacy_setups.id')
            ->leftJoin('meetings', 'meeting_minutes.meeting_id', '=', 'meetings.id')
            ->leftJoin('users as preparer', 'meeting_minutes.prepared_by', '=', 'preparer.id')
            ->leftJoin('users as reviewer', 'meeting_minutes.reviewed_by', '=', 'reviewer.id')
            ->with(['images', 'documents'])
            ->where('meeting_minutes.id', $id)->first();
        if (!$meetingMinute) {
            return response()->json(['status' => false, 'message' => 'Meeting minutes not found'], 404);
        }
        $meetingMinute->images = $meetingMinute->images->map(function ($image) {
            $image->image_url = $image->file_path ? url(Storage::url($image->file_path)) : null;
            return $image;
        });
        $meetingMinute->documents = $meetingMinute->documents->map(function ($document) {
            $document->document_url = $document->file_path ? url(Storage::url($document->file_path)) : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $meetingMinute], 200);
    }

    public function store(Request $request)
    {
        // The meeting must belong to this organisation
        $this->ensureOwnedParent(Meeting::class, $request->input('meeting_id'));

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $meetingMinutes = new MeetingMinutes();
        $meetingMinutes->meeting_id = $request->meeting_id;
        $meetingMinutes->prepared_by = $request->user()->id;
        $meetingMinutes->reviewed_by = $request->user()->id;
        $this->fill($meetingMinutes, $request);
        $meetingMinutes->save();
        $this->saveFiles($request, $meetingMinutes);

        return response()->json([
            'status' => true,
            'data' => $meetingMinutes,
            'message' => 'Meeting Minutes created successfully.'
        ], 201);
    }

    public function edit(MeetingMinutes $meetingMinutes) {}

    public function update(Request $request, $id)
    {
        // The meeting must belong to this organisation
        if ($request->has('meeting_id')) $this->ensureOwnedParent(Meeting::class, $request->input('meeting_id'));

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $meetingMinutes = $this->ownedVia(MeetingMinutes::class, 'meeting_id', Meeting::class)->find($id);
        if (!$meetingMinutes) {
            return response()->json(['status' => false, 'message' => 'Meeting minutes not found'], 404);
        }
        try {
            $meetingMinutes->meeting_id = $request->meeting_id;
            // The original writer stays; the person saving this version is the reviewer
            $meetingMinutes->reviewed_by = $request->user()->id;
            $this->fill($meetingMinutes, $request);
            $meetingMinutes->save();
            $this->saveFiles($request, $meetingMinutes);

            return response()->json([
                'status' => true,
                'data' => $meetingMinutes,
                'message' => 'Meeting Minutes updated successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating Meeting Minutes: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    public function destroy($id)
    {
        $meetingMinutes = $this->ownedVia(MeetingMinutes::class, 'meeting_id', Meeting::class)->with(['images', 'documents'])->find($id);
        if (!$meetingMinutes) {
            return response()->json(['status' => false, 'message' => 'Meeting minutes not found'], 404);
        }
        try {
            $paths = collect([$meetingMinutes->file_attachments])
                ->merge($meetingMinutes->images->pluck('file_path'))
                ->merge($meetingMinutes->documents->pluck('file_path'))
                ->filter();
            $meetingMinutes->images()->delete();
            $meetingMinutes->documents()->delete();
            $meetingMinutes->delete();
            foreach ($paths as $path) {
                Storage::disk('public')->delete($path);
            }
            return response()->json(['status' => true, 'message' => 'Meeting Minutes deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting Meeting Minutes: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    private function rules(): array
    {
        return [
            'meeting_id' => 'required|integer|exists:meetings,id',
            'privacy_setup_id' => 'required|integer|exists:privacy_setups,id',
            'minutes' => 'nullable|string',
            'decisions' => 'nullable|string',
            'note' => 'nullable|string',
            'follow_up_tasks' => 'nullable|string',
            'action_items' => 'nullable|string',
            'tags' => 'nullable|string|max:255',
            'meeting_location' => 'nullable|string|max:255',
            'video_link' => 'nullable|url|max:255',
            'start_time' => 'nullable|date_format:H:i,H:i:s',
            'end_time' => 'nullable|date_format:H:i,H:i:s',
            'approval_status' => 'nullable|integer|min:0|max:2',
            'is_publish' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ];
    }

    private function fill(MeetingMinutes $meetingMinutes, Request $request): void
    {
        foreach (self::TEXT_FIELDS as $field) {
            $meetingMinutes->{$field} = $request->input($field);
        }
        $meetingMinutes->privacy_setup_id = $request->privacy_setup_id;
        // Required columns: fall back to "draft, not published, active"
        $meetingMinutes->approval_status = (int) ($request->input('approval_status') ?? 0);
        $meetingMinutes->is_publish = $request->boolean('is_publish');
        $meetingMinutes->is_active = $request->has('is_active') ? $request->boolean('is_active') : true;
    }

    private function saveFiles(Request $request, MeetingMinutes $meetingMinutes): void
    {
        foreach ((array) $request->file('documents', []) as $document) {
            $path = $document->storeAs('org/meeting-minute/file', Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(), 'public');
            MeetingMinuteFile::create([
                'meeting_minute_id' => $meetingMinutes->id,
                'file_path' => $path,
                'file_name' => $document->getClientOriginalName(),
                'mime_type' => $document->getClientMimeType(),
                'file_size' => $document->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);
        }
        foreach ((array) $request->file('images', []) as $image) {
            $path = $image->storeAs('org/meeting-minute/image', Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(), 'public');
            MeetingMinuteImage::create([
                'meeting_minute_id' => $meetingMinutes->id,
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
