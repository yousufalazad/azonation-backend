<?php
namespace App\Http\Controllers\Org\Recognition;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Recognition;
use App\Models\RecognitionFile;
use App\Models\RecognitionImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecognitionController extends Controller
{
    public function index()
    {
        try {
            $recognitions = Recognition::get();
            return response()->json([
                'status' => true,
                'data' => $recognitions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function show($id)
    {
        $recognition =  Recognition::where('id', $id)
            ->first();
        if (!$recognition) {
            return response()->json(['status' => false, 'message' => 'Recognition not found'], 404);
        }
        $recognition->images = $recognition->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });
        $recognition->documents = $recognition->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $recognition], 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'recognition_date' => 'required|date',
            'privacy_setup_id' => 'required|integer',
            'status' => 'required|integer',
        ]);
        try {
            $recognition = Recognition::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'recognition_date' => $request->recognition_date,
                'privacy_setup_id' => $request->privacy_setup_id,
                'status' => $request->status,
            ]);
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/recognition/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    RecognitionFile::create([
                        'recognition_id' => $recognition->id,
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
                        'org/recognition/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    RecognitionImage::create([
                        'recognition_id' => $recognition->id,
                        'file_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Recognition created successfully',
                'data' => $recognition
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Catch error: An error occurred. Please try again.'
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'recognition_date' => 'required|date',
            'privacy_setup_id' => 'required|integer',
            'status' => 'required|integer',
        ]);
        try {
            $recognition = Recognition::where('id', $id)->first();
            if (!$recognition) {
                return response()->json([
                    'status' => false,
                    'message' => 'Recognition not found'
                ], 404);
            }
            $recognition->update([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'recognition_date' => $request->recognition_date,
                'privacy_setup_id' => $request->privacy_setup_id,
                'status' => $request->status,
            ]);
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/recognition/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    RecognitionFile::create([
                        'recognition_id' => $recognition->id,
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
                        'org/recognition/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    RecognitionImage::create([
                        'recognition_id' => $recognition->id,
                        'file_path' => $imagePath,
                        'file_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getClientMimeType(),
                        'file_size' => $image->getSize(),
                        'is_public' => true,
                        'is_active' => true,
                    ]);
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Recognition updated successfully',
                'data' => $recognition
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $recognition = Recognition::where('id', $id)->first();
            if (!$recognition) {
                return response()->json([
                    'status' => false,
                    'message' => 'Recognition not found'
                ], 404);
            }
            $recognition->delete();
            return response()->json([
                'status' => true,
                'message' => 'Recognition deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
