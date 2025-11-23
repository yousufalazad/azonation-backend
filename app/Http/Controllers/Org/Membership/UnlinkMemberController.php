<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Controllers\Controller;
use App\Models\UnlinkMemberImage;
use App\Models\UnlinkMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UnlinkMemberController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Inside index');

        $userId = Auth::id();

        $unlinkMembers = UnlinkMember::with(['image', 'membershipStatus', 'membershipType'])
            ->where('user_id', $userId)
            ->get();

        if ($unlinkMembers->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Unlink Members not found'], 404);
        }

        $unlinkMembers = $unlinkMembers->map(function ($member) {
            if ($member->image) {
                $member->image_url = $member->image->file_path
                    ? url(Storage::url($member->image->file_path))
                    : null;
            }
            unset($member->image);

            return $member;
        });

        return response()->json([
            'status' => true,
            'data' => $unlinkMembers
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'existing_membership_id' => 'nullable',
            'membership_type_id' => 'nullable',
            'membership_start_date' => 'nullable',
            'membership_status_id' => 'nullable',
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20048',
        ]);

        $validatedData['user_id'] = $request->user()->id;
        $unlinkMember = UnlinkMember::create($validatedData);

        Log::info("unlinkMember updated");

        if ($request->hasFile('image_path')) {
            Log::info("inside image upload");

            $image = $request->file('image_path'); // ⬅️ Single file only

            $imagePath = $image->storeAs(
                'org/unlink_member/image',
                Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                'public'
            );
            Log::info('Image is now available');

            UnlinkMemberImage::create([
                'unlink_member_id' => $unlinkMember->id,
                'file_path' => $imagePath,
                'file_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getClientMimeType(),
                'file_size' => $image->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);

            Log::info('Done');
        }
        return response()->json(['status' => true, 'message' => 'unlink Member created successfully.', 'data' => $unlinkMember], 201);
    }
    
    public function show($id)
    {
        // $unlinkMember = UnlinkMember::with('image')->find($id);
        $unlinkMember = UnlinkMember::with(['image', 'membershipStatus', 'membershipType'])
            ->where('id', $id)
            ->first();

        if (!$unlinkMember) {
            return response()->json(['status' => false, 'message' => 'Unlink Member not found.'], 404);
        }

        // Attach image_url if image exists
        $unlinkMember->image_url = $unlinkMember->image && $unlinkMember->image->file_path
            ? url(Storage::url($unlinkMember->image->file_path))
            : null;

        // Optionally hide the actual image relationship
        unset($unlinkMember->image);

        return response()->json([
            'status' => true,
            'data' => $unlinkMember,
        ]);
    }

    public function update(Request $request, $id)
    {
        $unlinkMember = UnlinkMember::find($id);
        if (!$unlinkMember) {
            return response()->json(['status' => false, 'message' => 'Unlink Member not found.'], 404);
        }

        $validatedData = $request->validate([
            'existing_membership_id' => 'nullable',
            'membership_type_id' => 'nullable',
            'membership_start_date' => 'nullable',
            'membership_status_id' => 'nullable',
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $validatedData['user_id'] = $request->user()->id;

        // Handle new image upload
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');

            // Delete old image files from storage
            $oldImages = UnlinkMemberImage::where('unlink_member_id', $unlinkMember->id)->get();
            foreach ($oldImages as $oldImage) {
                Storage::disk('public')->delete($oldImage->file_path);
                $oldImage->delete();
            }

            // Upload new image
            $timestamp = Carbon::now()->format('YmdHis');
            $newFileName = $timestamp . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('org/unlink_member/image', $newFileName, 'public');

            // Save new image record
            UnlinkMemberImage::create([
                'unlink_member_id' => $unlinkMember->id,
                'file_path' => $path,
                'file_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getClientMimeType(),
                'file_size' => $image->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);
        }

        // Update unlink member info
        $unlinkMember->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Unlink Member updated successfully.',
            'data' => $unlinkMember
        ]);
    }

    public function destroy($id)
    {
        $unlinkMember = UnlinkMember::find($id);
        if (!$unlinkMember) {
            return response()->json(['status' => false, 'message' => 'unlink Member not found.'], 404);
        }
        if ($unlinkMember->image_path) {
            Storage::disk('public')->delete($unlinkMember->image_path);
        }
        $unlinkMember->delete();
        return response()->json(['status' => true, 'message' => 'Unlink member deleted successfully.']);
    }
}
