<?php
namespace App\Http\Controllers\Org\SuccessStory;
use App\Http\Controllers\Controller;

use App\Models\SuccessStory;
use App\Models\SuccessStoryFile;
use App\Models\SuccessStoryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuccessStoryController extends Controller
{
    public function index()
    {
        try {
            $stories = SuccessStory::with('user:id,name')->get();
            return response()->json([
                'status' => true,
                'data' => $stories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function show($id)
    {
        $successStory =  SuccessStory::where('id', $id)
            ->with(['images', 'documents'])
            ->first();
        if (!$successStory) {
            return response()->json(['status' => false, 'message' => 'Strategic Plan not found'], 404);
        }
        $successStory->images = $successStory->images->map(function ($image) {
            $image->image_url = $image->file_path
                ? url(Storage::url($image->file_path))
                : null;
            return $image;
        });
        $successStory->documents = $successStory->documents->map(function ($document) {
            $document->document_url = $document->file_path
                ? url(Storage::url($document->file_path))
                : null;
            return $document;
        });
        return response()->json(['status' => true, 'data' => $successStory], 200);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'story' => 'required|string',
            'status' => 'required|boolean',
        ]);
        try {
            $story = new SuccessStory();
            $story->user_id = $request->user()->id;
            $story->title = $validated['title'];
            $story->story = $validated['story'];
            $story->status = $validated['status'];
            $story->save();
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/success-story',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    SuccessStoryFile::create([
                        'success_story_id' => $story->id,
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
                        'org/image/success-story',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    SuccessStoryImage::create([
                        'success_story_id' => $story->id,
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
                'message' => 'Success story created successfully.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'story' => 'required|string',
            'status' => 'required|boolean',
        ]);
        try {
            $story = SuccessStory::find($id);
            if (!$story) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record not found.'
                ], 404);
            }
            $story->title = $validated['title'];
            $story->story = $validated['story'];
            $story->status = $validated['status'];
            $story->save();
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    $documentPath = $document->storeAs(
                        'org/doc/success-story',
                        Carbon::now()->format('YmdHis') . '_' . $document->getClientOriginalName(),
                        'public'
                    );
                    SuccessStoryFile::create([
                        'success_story_id' => $story->id,
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
                        'org/image/success-story',
                        Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                        'public'
                    );
                    SuccessStoryImage::create([
                        'success_story_id' => $story->id,
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
                'message' => 'Success story updated successfully.'
            ], 200);
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
            $story = SuccessStory::find($id);
            if (!$story) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record not found.'
                ], 404);
            }
            $story->delete();
            return response()->json([
                'status' => true,
                'message' => 'Success story deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
