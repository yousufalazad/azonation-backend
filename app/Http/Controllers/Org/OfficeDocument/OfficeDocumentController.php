<?php

namespace App\Http\Controllers\Org\OfficeDocument;

use App\Http\Concerns\ResolvesCurrentOrg;
use Illuminate\Routing\Controller;
use App\Models\OfficeDocument;
use App\Models\OfficeDocumentFile;
use App\Models\OfficeDocumentImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * The organisation's documents: each entry has a title and one or more files
 * (constitution, registration papers, letters, photos of certificates...).
 */
class OfficeDocumentController extends Controller
{
    use ResolvesCurrentOrg;

    public function __construct()
    {
        $this->middleware('org.permission:document.read')->only(['index', 'show']);
        $this->middleware('org.permission:document.create')->only(['create', 'store']);
        $this->middleware('org.permission:document.update')->only(['edit', 'update', 'destroyFile']);
        $this->middleware('org.permission:document.delete')->only(['destroy']);
    }

    // The current organisation's documents with how many files each has
    public function index()
    {
        $documents = $this->owned(OfficeDocument::class)
            ->select('office_documents.*', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'office_documents.privacy_setup_id', '=', 'privacy_setups.id')
            ->withCount(['documents', 'images'])
            ->orderByDesc('office_documents.date')
            ->orderByDesc('office_documents.id')
            ->get();
        return response()->json(['status' => true, 'data' => $documents], 200);
    }

    public function show($documentId)
    {
        $document = $this->owned(OfficeDocument::class)
            ->select('office_documents.*', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'office_documents.privacy_setup_id', '=', 'privacy_setups.id')
            ->with(['images', 'documents'])
            ->where('office_documents.id', $documentId)
            ->first();
        if (!$document) {
            return response()->json(['status' => false, 'message' => 'Office document not found'], 404);
        }
        $document->images = $document->images->map(function ($image) {
            $image->image_url = $image->image_path ? url(Storage::url($image->image_path)) : null;
            return $image;
        });
        $document->documents = $document->documents->map(function ($file) {
            $file->document_url = $file->file_path ? url(Storage::url($file->file_path)) : null;
            return $file;
        });
        return response()->json(['status' => true, 'data' => $document], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $document = DB::transaction(function () use ($request) {
            $document = new OfficeDocument($this->values($request));
            $document->user_id = $this->orgIdOrFail(); // always the current organisation
            $document->save();
            $this->saveFiles($request, $document);
            return $document;
        });
        return response()->json(['status' => true, 'message' => 'Document added successfully.', 'data' => $document], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $document = $this->owned(OfficeDocument::class)->find($id);
        if (!$document) {
            return response()->json(['status' => false, 'message' => 'Office document not found'], 404);
        }
        // The owner never changes (before, an admin saving a document made it theirs)
        DB::transaction(function () use ($request, $document) {
            $document->update($this->values($request));
            $this->saveFiles($request, $document);
        });
        return response()->json(['status' => true, 'message' => 'Document updated successfully.', 'data' => $document], 200);
    }

    // Remove one attached file: DELETE /office-documents/{id}/files/{fileId}?kind=image|document
    public function destroyFile(Request $request, $id, $fileId)
    {
        $document = $this->owned(OfficeDocument::class)->find($id);
        if (!$document) {
            return response()->json(['status' => false, 'message' => 'Office document not found'], 404);
        }
        $isImage = $request->query('kind') === 'image';
        $file = ($isImage ? $document->images() : $document->documents())->find($fileId);
        if (!$file) {
            return response()->json(['status' => false, 'message' => 'File not found'], 404);
        }
        Storage::disk('public')->delete($isImage ? $file->image_path : $file->file_path);
        $file->delete();
        return response()->json(['status' => true, 'message' => 'File removed.'], 200);
    }

    public function destroy($id)
    {
        $document = $this->owned(OfficeDocument::class)->with(['images', 'documents'])->find($id);
        if (!$document) {
            return response()->json(['status' => false, 'message' => 'Office document not found'], 404);
        }
        $paths = $document->images->pluck('image_path')->merge($document->documents->pluck('file_path'))->filter();
        DB::transaction(function () use ($document) {
            $document->images()->delete();
            $document->documents()->delete();
            $document->delete();
        });
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }
        return response()->json(['status' => true, 'message' => 'Document deleted successfully.'], 200);
    }

    private function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'privacy_setup_id' => 'nullable|integer|exists:privacy_setups,id',
            'is_active' => 'nullable|boolean',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:10240',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:20480',
        ];
    }

    private function values(Request $request): array
    {
        return [
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            // Required by the table: default to Private when nothing is chosen
            'privacy_setup_id' => $request->privacy_setup_id
                ?: (DB::table('privacy_setups')->where('name', 'Private')->value('id') ?? DB::table('privacy_setups')->orderBy('id')->value('id')),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];
    }

    private function saveFiles(Request $request, OfficeDocument $document): void
    {
        foreach ((array) $request->file('documents', []) as $upload) {
            $path = $upload->storeAs('org/office-document/file', Carbon::now()->format('YmdHis') . '_' . $upload->getClientOriginalName(), 'public');
            OfficeDocumentFile::create([
                'office_document_id' => $document->id,
                'file_path' => $path,
                'file_name' => $upload->getClientOriginalName(),
                'mime_type' => $upload->getClientMimeType(),
                'file_size' => $upload->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);
        }
        foreach ((array) $request->file('images', []) as $upload) {
            $path = $upload->storeAs('org/office-document/image', Carbon::now()->format('YmdHis') . '_' . $upload->getClientOriginalName(), 'public');
            OfficeDocumentImage::create([
                'office_document_id' => $document->id,
                'image_path' => $path,
                'file_name' => $upload->getClientOriginalName(),
                'mime_type' => $upload->getClientMimeType(),
                'file_size' => $upload->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);
        }
    }
}
