<?php
namespace App\Http\Controllers\Org\OfficeDocument;
use App\Http\Controllers\Controller;

use App\Models\OfficeDocument;
use App\Models\OfficeDocumentFile;
use App\Models\OfficeDocumentImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfficeDocumentController extends Controller
{
    public function index()
    {
        try {
            $officeRecords = OfficeDocument::with(['images', 'documents'])->get();
            return response()->json([
                'status' => true,
                'message' => 'Office documents fetched successfully!',
                'data' => $officeRecords
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show($documentId)
    {
        $document = OfficeDocument::with(['images', 'documents'])->find($documentId);
        if (!$document) {
            return response()->json(['status' => false, 'message' => 'Office document not found'], 404);
        }
        $document->images = $document->images->map(function ($image) {
            $image->image_url = $image->image_path
                ? url(Storage::url($image->image_path))
                : null;
            return $image;
        });
        $document->documents = $document->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $document], 200);
    }
    public function store(Request $request)
    {
        // dd($request->all());exit;
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:20000',
            'privacy_setup_id' => 'nullable|integer',
            'is_active' => 'nullable|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1000240',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:1000240',

        ]);
        $user_id = $request->user()->id;
        DB::beginTransaction();
        try {
            $officeDocument = new OfficeDocument();
            $officeDocument->title = $validatedData['title'];
            $officeDocument->description = $validatedData['description'];
            $officeDocument->privacy_setup_id = $validatedData['privacy_setup_id'];
            $officeDocument->user_id = $user_id;
            $officeDocument->is_active = $validatedData['is_active'];
            $officeDocument->save();
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/office-document/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    OfficeDocumentFile::create([
                        'office_document_id' => $officeDocument->id,
                        'file_path' => $documentPath,
                        'file_name' => $document->getClientOriginalName(),
                        'mime_type' => $document->getClientMimeType(),
                        'file_size' => $document->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/office-document/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    OfficeDocumentImage::create([
                        'office_document_id' => $officeDocument->id,
                        'image_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Organizational office document added successfully.',
                'data' => $officeDocument
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:20000',
            'privacy_setup_id' => 'nullable|integer',
            'is_active' => 'nullable|integer',
            // 'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);
        $user_id = $request->user()->id;
        DB::beginTransaction();
        try {
            $officeDocument = OfficeDocument::findOrFail($id);
            $officeDocument->title = $validatedData['title'];
            $officeDocument->description = $validatedData['description'];
            $officeDocument->privacy_setup_id = $validatedData['privacy_setup_id'];
            $officeDocument->is_active = $validatedData['is_active'];
            $officeDocument->user_id = $user_id;
            $officeDocument->save();
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/office-document/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    OfficeDocumentFile::create([
                        'office_document_id' => $officeDocument->id,
                        'file_path' => $documentPath,
                        'file_name' => $document->getClientOriginalName(),
                        'mime_type' => $document->getClientMimeType(),
                        'file_size' => $document->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->storeAs(
                        'org/office-document/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    OfficeDocumentImage::create([
                        'office_document_id' => $officeDocument->id,
                        'image_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Organizational office document updated successfully.',
                'data' => $officeDocument
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $officeDocument = OfficeDocument::findOrFail($id);
            if ($officeDocument->document) {
                Storage::delete('public/' . $officeDocument->document);
            }
            $allDocuments = OfficeDocumentFile::where('office_document_id', $id)->get();
            foreach ($allDocuments as $singleDocument) {
                Storage::delete('public/' . $singleDocument->file_path);
                $singleDocument->delete();
            }
            $allImages = OfficeDocumentImage::where('office_document_id', $id)->get();
            foreach ($allImages as $singleImage) {
                Storage::delete('public/' . $singleImage->image_path);
                $singleImage->delete();
            }
            $officeDocument->delete();
            return response()->json([
                'status' => true,
                'message' => 'Organizational history deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
