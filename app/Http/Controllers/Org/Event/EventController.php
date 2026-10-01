<?php
namespace App\Http\Controllers\Org\Event;

use App\Http\Concerns\ResolvesCurrentOrg;
use Illuminate\Routing\Controller;
use App\Models\Event;
use App\Models\EventFile;
use App\Models\EventImage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Services\MemberFamilies;

class EventController extends Controller
{
    use ResolvesCurrentOrg;

    // Fields the form may send; the organisation and ids are set by the server
    private const FIELDS = [
        'title', 'name', 'short_description', 'description', 'date', 'time',
        'venue_name', 'venue_address', 'requirements', 'note', 'status', 'conduct_type', 'family_welcome',
    ];

    public function __construct()
    {
        $this->middleware('org.permission:event.read')->only(['index', 'getEvent']);
        $this->middleware('org.permission:event.create')->only(['store']);
        $this->middleware('org.permission:event.update')->only(['update']);
        $this->middleware('org.permission:event.delete')->only(['destroy']);
    }

    public function index()
    {
        // The current organisation's events (not the signed-in person's own id,
        // which is wrong for admins acting for an organisation)
        $events = $this->owned(Event::class)
            ->select('events.*', 'conduct_types.name as conduct_type_name')
            ->leftJoin('conduct_types', 'events.conduct_type', '=', 'conduct_types.id')
            ->get();
        return response()->json(['status' => true, 'data' => $events]);
    }

    public function getEvent($eventId)
    {
        $event = $this->owned(Event::class)
            ->select('events.*', 'conduct_types.name as conduct_type_name')
            ->leftJoin('conduct_types', 'events.conduct_type', '=', 'conduct_types.id')
            ->where('events.id', $eventId)
            ->first();
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found'], 404);
        }
        $event->images = $event->images->map(function ($image) {
            $image->image_url = $image->image_path ? url(Storage::url($image->image_path)) : null;
            return $image;
        });
        $event->documents = $event->documents->map(function ($document) {
            $document->document_url = $document->file_path ? url(Storage::url($document->file_path)) : null;
            return $document;
        });
        // Families welcome: an estimate from the families members chose to share (totals only, no names)
        $event->family_estimate = $event->family_welcome ? MemberFamilies::estimate((int) $event->user_id) : null;
        return response()->json(['status' => true, 'data' => $event], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $event = new Event($this->values($request));
        $event->user_id = $this->orgIdOrFail(); // always the current organisation
        $event->save();
        $this->saveFiles($request, $event);
        return response()->json(['status' => true, 'message' => 'Event created successfully.', 'data' => $event], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $event = $this->owned(Event::class)->find($id);
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found.'], 404);
        }
        // The owner never changes (before, an admin saving an event made it theirs)
        $event->update($this->values($request));
        $this->saveFiles($request, $event);
        return response()->json(['status' => true, 'message' => 'Event updated successfully.', 'data' => $event]);
    }

    public function destroy($id)
    {
        $event = $this->owned(Event::class)->with(['images', 'documents'])->find($id);
        if (!$event) {
            return response()->json(['status' => false, 'message' => 'Event not found.'], 404);
        }
        $paths = $event->images->pluck('image_path')->merge($event->documents->pluck('file_path'))->filter();
        $event->images()->delete();
        $event->documents()->delete();
        $event->delete();
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
        return response()->json(['status' => true, 'message' => 'Event deleted successfully.']);
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i,H:i:s',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string|max:255',
            'requirements' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'status' => 'nullable|in:0,1', // 0 = active, 1 = disabled
            'conduct_type' => 'nullable|exists:conduct_types,id',
            'family_welcome' => 'nullable|boolean',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ];
    }

    private function values(Request $request): array
    {
        $values = collect(self::FIELDS)->mapWithKeys(fn ($f) => [$f => $request->input($f)])->all();
        // The table needs a title; the form asks for a name
        $values['title'] = $values['title'] ?: $values['name'];
        $values['status'] = (int) ($values['status'] ?? 0);
        $values['family_welcome'] = $request->boolean('family_welcome');
        return $values;
    }

    private function saveFiles(Request $request, Event $event): void
    {
        foreach ((array) $request->file('documents', []) as $document) {
            $path = $document->storeAs('org/event/file', Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(), 'public');
            EventFile::create([
                'event_id' => $event->id,
                'file_path' => $path,
                'file_name' => $document->getClientOriginalName(),
                'mime_type' => $document->getClientMimeType(),
                'file_size' => $document->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);
        }
        foreach ((array) $request->file('images', []) as $image) {
            $path = $image->storeAs('org/event/image', Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(), 'public');
            EventImage::create([
                'event_id' => $event->id,
                'image_path' => $path,
                'file_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getClientMimeType(),
                'file_size' => $image->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);
        }
    }
}
