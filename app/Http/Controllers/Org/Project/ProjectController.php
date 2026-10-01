<?php
namespace App\Http\Controllers\Org\Project;

use App\Http\Concerns\ResolvesCurrentOrg;
use Illuminate\Routing\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Projects: work the organisation does over a period (a campaign, a relief drive...).
 */
class ProjectController extends Controller
{
    use ResolvesCurrentOrg;

    private const FIELDS = [
        'title', 'short_description', 'description', 'start_date', 'end_date', 'start_time', 'end_time',
        'venue_name', 'venue_address', 'requirements', 'note', 'conduct_type',
    ];

    public function __construct()
    {
        $this->middleware('org.permission:project.read')->only(['index', 'show']);
        $this->middleware('org.permission:project.create')->only(['create', 'store']);
        $this->middleware('org.permission:project.update')->only(['edit', 'update']);
        $this->middleware('org.permission:project.delete')->only(['destroy']);
    }

    public function index()
    {
        // The current organisation's projects (not the signed-in person's own id)
        $projects = $this->owned(Project::class)
            ->select('projects.*', 'conduct_types.name as conduct_type_name')
            ->leftJoin('conduct_types', 'projects.conduct_type', '=', 'conduct_types.id')
            ->orderBy('projects.id', 'desc')
            ->get();
        return response()->json(['status' => true, 'data' => $projects]);
    }

    public function create() {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $project = new Project($this->values($request));
        $project->user_id = $this->orgIdOrFail(); // always the current organisation
        $project->save();
        $this->saveFiles($request, $project);
        return response()->json(['status' => true, 'message' => 'Project created successfully', 'data' => $project], 201);
    }

    public function show($projectId)
    {
        $project = $this->owned(Project::class)
            ->select('projects.*', 'conduct_types.name as conduct_type_name')
            ->leftJoin('conduct_types', 'projects.conduct_type', '=', 'conduct_types.id')
            ->with(['images', 'documents'])
            ->where('projects.id', $projectId)
            ->first();
        if (!$project) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        $project->images = $project->images->map(function ($image) {
            $image->image_url = $image->image_path ? url(Storage::url($image->image_path)) : null;
            return $image;
        });
        $project->documents = $project->documents->map(function ($document) {
            $document->document_url = $document->file_path ? url(Storage::url($document->file_path)) : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $project], 200);
    }

    public function edit($id) {}

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $project = $this->owned(Project::class)->find($id);
        if (!$project) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        $project->update($this->values($request));
        $this->saveFiles($request, $project);
        return response()->json(['status' => true, 'message' => 'Project updated successfully', 'data' => $project], 200);
    }

    public function destroy($id)
    {
        $project = $this->owned(Project::class)->with(['images', 'documents'])->find($id);
        if (!$project) {
            return response()->json(['status' => false, 'message' => 'Project not found'], 404);
        }
        $paths = $project->images->pluck('image_path')->merge($project->documents->pluck('file_path'))->filter();
        $project->images()->delete();
        $project->documents()->delete();
        $project->delete();
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
        return response()->json(['status' => true, 'message' => 'Project deleted successfully.'], 200);
    }

    private function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            // Formatted text; the pages clean it before showing it
            'description' => 'nullable|string|max:60000',
            'requirements' => 'nullable|string|max:60000',
            'note' => 'nullable|string|max:60000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i,H:i:s',
            'end_time' => 'nullable|date_format:H:i,H:i:s',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'conduct_type' => 'nullable|exists:conduct_types,id',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240',
        ];
    }

    private function values(Request $request): array
    {
        $values = collect(self::FIELDS)->mapWithKeys(fn ($f) => [$f => $request->input($f)])->all();
        $values['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        return $values;
    }

    private function saveFiles(Request $request, Project $project): void
    {
        foreach ((array) $request->file('documents', []) as $document) {
            $path = $document->storeAs('org/project/file', Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(), 'public');
            ProjectFile::create([
                'project_id' => $project->id,
                'file_path' => $path,
                'file_name' => $document->getClientOriginalName(),
                'mime_type' => $document->getClientMimeType(),
                'file_size' => $document->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);
        }
        foreach ((array) $request->file('images', []) as $image) {
            $path = $image->storeAs('org/project/image', Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(), 'public');
            ProjectImage::create([
                'project_id' => $project->id,
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
