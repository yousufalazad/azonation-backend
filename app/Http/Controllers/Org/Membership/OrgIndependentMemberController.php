<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Controllers\Controller;
use App\Models\IndependentMemberImage;
use App\Models\OrgIndependentMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrgIndependentMemberController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Inside index');
        $userId = Auth::id();
        $independentMembers = OrgIndependentMember::where('user_id', $userId)->get();
        $independentMembers = $independentMembers->map(function ($independentMember) {
            $independentMember->image_url = $independentMember->image_path
                ? url(Storage::url($independentMember->image_path))
                : null;
            return $independentMember;
        });
        return response()->json(['status' => true, 'data' => $independentMembers]);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $validatedData['user_id'] = $request->user()->id;
        $independentMember = OrgIndependentMember::create($validatedData);

        Log::info("independentMember updated");

        if ($request->hasFile('image_path')) {
            Log::info("inside image upload");
            foreach ($request->file('image_path') as $image) {
                $imagePath = $image->storeAs(
                    'org/independent-independentMember/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                Log::info('Image is now available');
                IndependentMemberImage::create([
                    'org_independent_member_id' => $independentMember->id,
                    'file_path' => $imagePath,
                    'file_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getClientMimeType(),
                    'file_size' => $image->getSize(),
                    'is_public' => true,
                    'is_active' => true,
                ]);
            }
            Log::info('Done');
        }
        return response()->json(['status' => true, 'message' => 'independentMember created successfully.', 'data' => $independentMember], 201);
    }
    public function show($id)
    {
        $independentMember = OrgIndependentMember::find($id);
        if (!$independentMember) {
            return response()->json(['status' => false, 'message' => 'independentMember not found.'], 404);
        }
        $imageUrl = $independentMember ? Storage::url($independentMember->image_path) : null;
        return response()->json([
            'status' => true,
            'data' => $independentMember,
            'imageUrl' => $imageUrl,
        ]);
    }
    public function update(Request $request, $id)
    {
        $independentMember = OrgIndependentMember::find($id);
        if (!$independentMember) {
            return response()->json(['status' => false, 'message' => 'independentMember not found.'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($request->hasFile('image_path')) {
            if ($independentMember->image_path) {
                Storage::disk('public')->delete($independentMember->image_path);
            }
            $image = $request->file('image_path');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $timestamp = Carbon::now()->format('YmdHis');
            $newFileName = $timestamp . '_' . $originalName . '.' . $extension;
            $path = $image->storeAs('org/independent_member/image', $newFileName, 'public');
            $validatedData['image_path'] = $path;
        }
        $validatedData['user_id'] = $request->user()->id;
        $independentMember->update($validatedData);
        return response()->json(['status' => true, 'message' => 'independentMember updated successfully.', 'data' => $independentMember]);
    }
    public function destroy($id)
    {
        $independentMember = OrgIndependentMember::find($id);
        if (!$independentMember) {
            return response()->json(['status' => false, 'message' => 'independentMember not found.'], 404);
        }
        if ($independentMember->image_path) {
            Storage::disk('public')->delete($independentMember->image_path);
        }
        $independentMember->delete();
        return response()->json(['status' => true, 'message' => 'Independent member deleted successfully.']);
    }
}
