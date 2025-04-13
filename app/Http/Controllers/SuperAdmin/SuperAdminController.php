<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use App\Models\ProfileImage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SuperAdminController extends Controller
{

    public function getSuperAdminProfileImage($userId)
    {
        $logo = ProfileImage::where('user_id', $userId)->orderBy('id', 'desc')->first();
        $imageUrl = $logo ? Storage::url($logo->image_path) : null;
        return response()->json([
            'status' => true,
            'data' => ['image' => $imageUrl]
        ]);
    }
    public function updateSuperAdminProfileImage(Request $request)
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
        $path = $image->storeAs('super_admin/profile/image', $newFileName, 'public');
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

    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show($id)
    {
        $superAdminUserData = SuperAdmin::where('user_id', $id)->first();
        if ($superAdminUserData) {
            return response()->json(['status' => true, 'data' => $superAdminUserData]);
        } else {
            return response()->json(['status' => false, 'message' => 'SuperAdmin not found']);
        }
    }
    public function edit(SuperAdmin $superAdmin) {}
    public function update(Request $request, SuperAdmin $superAdmin) {}
    public function destroy(SuperAdmin $superAdmin) {}
}
