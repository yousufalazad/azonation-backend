<?php
namespace App\Http\Controllers\Org\History;
use App\Http\Controllers\Controller;

use App\Models\History;
use App\Models\HistoryFile;
use App\Models\HistoryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoryController extends Controller
{
    public function index()
    {
        try {
            $userId = Auth::id();
            $histories = History::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
            return response()->json([
                'status' => true,
                'data' => $histories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve records.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show($id)
    {
        $history = History::where('id', $id)
            ->with(['images', 'documents'])
            ->first();
        if (!$history) {
            return response()->json(['status' => false, 'message' => 'History not found'], 404);
        }
        $history->images = $history->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });
        $history->documents = $history->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });

        // dd($history);exit;
        return response()->json(['status' => true, 'data' => $history], 200);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'history' => 'required|string|max:20000',
            'is_active' => 'required|integer',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:100240',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20048',
        ]);
        $history = new History();
        $history->user_id = Auth::id();
        $history->title = $validatedData['title'];
        $history->history = $validatedData['history'];
        $history->is_active = $validatedData['is_active'];
        $history->save();
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentPath = $document->storeAs(
                    'org/history/file',
                    Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                    'public'
                );
                HistoryFile::create([
                    'history_id' => $history->id,
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
                    'org/history/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                HistoryImage::create([
                    'history_id' => $history->id,
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
            'message' => 'Organizational history record added successfully.',
            'data' => $history
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'history' => 'required|string|max:20000',
            'is_active' => 'required|integer',
        ]);
        try {
            $history = History::findOrFail($id);
            $history->update([
                'title' => $validatedData['title'],
                'history' => $validatedData['history'],
                'is_active' => $validatedData['is_active'],
            ]);
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/history/file',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    HistoryFile::create([
                        'history_id' => $history->id,
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
                        'org/history/image',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    HistoryImage::create([
                        'history_id' => $history->id,
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
                'message' => 'Organizational history updated successfully.',
                'data' => $history,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $history = History::findOrFail($id);
            if ($history->image) {
                Storage::delete('public/' . $history->image);
            }
            if ($history->document) {
                Storage::delete('public/' . $history->document);
            }
            $history->delete();
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
