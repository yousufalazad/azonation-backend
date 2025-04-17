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
        $members = OrgIndependentMember::where('user_id', $userId)->get();
        $members = $members->map(function ($member) {
            $member->image_url = $member->image_path
                ? url(Storage::url($member->image_path))
                : null;
            return $member;
        });
        return response()->json(['status' => true, 'data' => $members]);
    }
    public function store(Request $request)
    {
        $validatedData['user_id'] = $request->user()->id;
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // 'email' => 'nullable|email|unique:org_independent_members,email',
            'email' => 'nullable|email', //multiple org can add same person with same email address
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'is_active' => 'required|boolean',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $member = new OrgIndependentMember();
        $member->user_id = $request->user()->id;
        $member->name = $validatedData['name'];
        $member->email = $validatedData['email'];
        $member->mobile = $validatedData['mobile'];
        $member->address = $validatedData['address'];
        $member->note = $validatedData['note'];
        $member->is_active = $validatedData['is_active'];
        $member->save();

        Log::info("Member updated");

        if ($request->hasFile('image_path')) {
            Log::info("inside image upload");
            foreach ($request->file('image_path') as $image) {
                $imagePath = $image->storeAs(
                    'org/independent-member/image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                Log::info('Image is now available');
                IndependentMemberImage::create([
                    'org_independent_member_id' => $member->id,
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
        return response()->json(['status' => true, 'message' => 'Member created successfully.', 'data' => $member], 201);
    }
    public function show($id)
    {
        $member = OrgIndependentMember::find($id);
        if (!$member) {
            return response()->json(['status' => false, 'message' => 'Member not found.'], 404);
        }
        $imageUrl = $member ? Storage::url($member->image_path) : null;
        return response()->json([
            'status' => true,
            'data' => $member,
            'imageUrl' => $imageUrl,
        ]);
    }
    public function update(Request $request, $id)
    {
        $member = OrgIndependentMember::find($id);
        if (!$member) {
            return response()->json(['status' => false, 'message' => 'Member not found.'], 404);
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            // 'email' => 'nullable|email|unique:org_independent_members,email',
            'email' => 'nullable|email', //multiple org can add same person with same email address
            'mobile' => 'string|max:15',
            'address' => 'nullable|string|max:500',
            'note' => 'nullable|string',
            'is_active' => 'required|boolean',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($request->hasFile('image_path')) {
            if ($member->image_path) {
                Storage::disk('public')->delete($member->image_path);
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
        $member->update($validatedData);
        return response()->json(['status' => true, 'message' => 'Member updated successfully.', 'data' => $member]);
    }
    public function destroy($id)
    {
        $member = OrgIndependentMember::find($id);
        if (!$member) {
            return response()->json(['status' => false, 'message' => 'Member not found.'], 404);
        }
        if ($member->image_path) {
            Storage::disk('public')->delete($member->image_path);
        }
        $member->delete();
        return response()->json(['status' => true, 'message' => 'Member deleted successfully.']);
    }
}
