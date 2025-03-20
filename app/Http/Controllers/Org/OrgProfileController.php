<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\OrgProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class OrgProfileController extends Controller
{
    protected function success($message, $data = [], $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }
    public function getLogo($userId)
    {
        $logo = ProfileImage::where('user_id', $userId)->orderBy('id', 'desc')->first();
        $imageUrl = $logo ? Storage::url($logo->image_path) : null;
        return response()->json([
            'status' => true,
            'data' => ['image' => $imageUrl]
        ]);
    }
    public function updateLogo(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20048',
        ]);
        $userId = $request->user()->id;
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Organization not found'], 404);
        }
        $image = $request->file('image');
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $mime_type = 'image' . '/' . $extension;
        $fileSize = $image->getSize();
        $timestamp = Carbon::now()->format('YmdHis');
        $newFileName = $timestamp . '_' . $originalName . '.' . $extension;
        $path = $image->storeAs('org/logo', $newFileName, 'public');
        $orgLogo = ProfileImage::where('user_id', $userId)->orderBy('id', 'desc')->first();
        if ($orgLogo) {
            $orgLogo->user_id = $userId;
            $orgLogo->image_path = $path;
            $orgLogo->file_name = $originalName;
            $orgLogo->mime_type = $mime_type;
            $orgLogo->file_size = $fileSize;
            $orgLogo->save();
        } else {
            $orgLogo = new ProfileImage();
            $orgLogo->user_id = $userId;
            $orgLogo->image_path = $path;
            $orgLogo->file_name = $originalName;
            $orgLogo->mime_type = $mime_type;
            $orgLogo->file_size = $fileSize;
            $orgLogo->save();
        }
        $imageUrl = Storage::url($path);
        return response()->json(['status' => true, 'data' => ['image' => $imageUrl]]);
    }
    public function index($userId)
    {
        $orgProfileData = OrgProfile::where('user_id', $userId)->first();
        if ($orgProfileData) {
            return response()->json(['status' => true, 'data' => $orgProfileData]);
        } else {
            return response()->json(['status' => false, 'message' => 'Organisation not found']);
        }
    }
    public function create() {}
    public function store(Request $request) {}
    public function show(OrgProfile $orgProfile) {}
    public function edit(OrgProfile $orgProfile) {}
    public function update(Request $request, int $userId): JsonResponse
    {
        $validatedData = $request->validate([
            'short_description'  => 'nullable|string|max:255',
            'detail_description' => 'nullable|string',
            'who_we_are' => 'nullable|string',
            'what_we_do' => 'nullable|string',
            'how_we_do' => 'nullable|string',
            'mission' => 'nullable|string',
            'vision' => 'nullable|string',
            'value' => 'nullable|string',
            'areas_of_focus' => 'nullable|string',
            'causes' => 'nullable|string',
            'impact' => 'nullable|string',
            'why_join_us' => 'nullable|string',
            'scope_of_work' => 'nullable|string',
            'organising_date' => 'nullable|date',
            'foundation_date' => 'nullable|date',
            'status' => 'nullable|boolean',
        ]);
        try {
            $orgProfile = OrgProfile::firstOrNew(['user_id' => $userId]);
            $orgProfile->fill($validatedData);
            $orgProfile->save();
            $message = $orgProfile->wasRecentlyCreated
                ? 'Org profile created successfully.'
                : 'Org profile updated successfully.';
            return response()->json([
                'status'  => true,
                'data'    => $orgProfile,
                'message' => $message,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update or create OrgProfile for user ID {$userId}: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }
    }
    public function destroy(OrgProfile $orgProfile) {}
}
