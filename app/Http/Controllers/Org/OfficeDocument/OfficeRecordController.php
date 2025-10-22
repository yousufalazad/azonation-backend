<?php
namespace App\Http\Controllers\Org\OfficeDocument;
use App\Http\Controllers\Controller;
use App\Models\OfficeRecord;
use App\Models\OfficeRecordImage;
use App\Models\OfficeRecordDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OfficeRecordController extends Controller
{
    public function index()
    {
        try {
            $officeRecords = OfficeRecord::with(['images', 'documents'])->get();
            return response()->json([
                'status' => true,
                'message' => 'Office records fetched successfully!',
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
    public function show($recordId)
    {
        $record = OfficeRecord::with(['images', 'documents'])->find($recordId);
        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found'], 404);
        }
        $record->images = $record->images->map(function ($image) {
            $image->image_url = $image->image_path
                ? url(Storage::url($image->image_path))
                : null;
            return $image;
        });
        $record->documents = $record->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $record], 200);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable',
            'privacy_setup_id' => 'nullable|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);
        $user_id = $request->user()->id;
        DB::beginTransaction();
        try {
            $officeRecord = new OfficeRecord();
            $officeRecord->title = $validatedData['title'];
            $officeRecord->description = $validatedData['description'];
            $officeRecord->privacy_setup_id = $validatedData['privacy_setup_id'];
            $officeRecord->user_id = $user_id;
            $officeRecord->save();
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/office-record/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    OfficeRecordDocument::create([
                        'office_record_id' => $officeRecord->id,
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
                        'org/office-record/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    OfficeRecordImage::create([
                        'office_record_id' => $officeRecord->id,
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
                'message' => 'Organizational office record added successfully.',
                'data' => $officeRecord
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
            'description' => 'nullable',
            'privacy_setup_id' => 'nullable|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);
        $user_id = $request->user()->id;
        DB::beginTransaction();
        try {
            $officeRecord = OfficeRecord::findOrFail($id);
            $officeRecord->title = $validatedData['title'];
            $officeRecord->description = $validatedData['description'];
            $officeRecord->privacy_setup_id = $validatedData['privacy_setup_id'];
            $officeRecord->user_id = $user_id;
            $officeRecord->save();
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/office-record/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    OfficeRecordDocument::create([
                        'office_record_id' => $officeRecord->id,
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
                        'org/office-record/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    OfficeRecordImage::create([
                        'office_record_id' => $officeRecord->id,
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
                'message' => 'Organizational office record updated successfully.',
                'data' => $officeRecord
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
            $officeRecord = OfficeRecord::findOrFail($id);
            if ($officeRecord->document) {
                Storage::delete('public/' . $officeRecord->document);
            }
            $allDocuments = OfficeRecordDocument::where('office_record_id', $id)->get();
            foreach ($allDocuments as $singleDocument) {
                Storage::delete('public/' . $singleDocument->file_path);
                $singleDocument->delete();
            }
            $allImages = OfficeRecordImage::where('office_record_id', $id)->get();
            foreach ($allImages as $singleImage) {
                Storage::delete('public/' . $singleImage->image_path);
                $singleImage->delete();
            }
            $officeRecord->delete();
            return response()->json([
                'status' => true,
                'message' => 'Organizational history deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
