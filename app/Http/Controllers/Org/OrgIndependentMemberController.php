<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\OrgIndependentMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; // Import Carbon for timestamp
class OrgIndependentMemberController extends Controller
{
    /**
     * Display a listing of the members.
     */
    public function index()
    {
        $members = OrgIndependentMember::all();

        // Map the members to include the full image URL with the running server path
        $members = $members->map(function ($member) {
            $member->image_url = $member->image_path
                ? url(Storage::url($member->image_path))
                : null;
            return $member;
        });

        return response()->json(['status' => true, 'data' => $members]);
    }
    /**
     * Store a newly created member in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:org_independent_members,email',
            'mobile' => 'required|string|max:15',
            'address' => 'nullable|string|max:500',
            'admin_note' => 'nullable|string',
            'is_active' => 'required|boolean',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image_path')) {

            $image = $request->file('image_path');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            // $mime_type = 'image_path' . '/' . $extension;
            // $fileSize = $image->getSize(); // Get file size in bytes
            $timestamp = Carbon::now()->format('YmdHis');
            $newFileName = $timestamp . '_' . $originalName . '.' . $extension;
            $path = $image->storeAs('org/independent_member', $newFileName, 'public');

            $validatedData['image_path'] = $path;
        }
        $validatedData['user_id'] = $request->user()->id;

        $member = OrgIndependentMember::create($validatedData);

        return response()->json(['status' => true, 'message' => 'Member created successfully.', 'data' => $member], 201);
    }

    /**
     * Display the specified member.
     */
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

    /**
     * Update the specified member in storage.
     */
    public function update(Request $request, $id)
    {
        $member = OrgIndependentMember::find($id);

        if (!$member) {
            return response()->json(['status' => false, 'message' => 'Member not found.'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:org_independent_members,email,{$id}",
            'mobile' => 'required|string|max:15',
            'address' => 'nullable|string|max:500',
            'admin_note' => 'nullable|string',
            'is_active' => 'required|boolean',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image_path')) {
            // Delete the old image if exists
            if ($member->image_path) {
                Storage::disk('public')->delete($member->image_path);
            }

            $image = $request->file('image_path');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            // $mime_type = 'image_path' . '/' . $extension;
            // $fileSize = $image->getSize(); // Get file size in bytes
            $timestamp = Carbon::now()->format('YmdHis');
            $newFileName = $timestamp . '_' . $originalName . '.' . $extension;
            $path = $image->storeAs('org/independent_member', $newFileName, 'public');
            $validatedData['image_path'] = $path;
        }
        $validatedData['user_id'] = $request->user()->id;
        $member->update($validatedData);

        return response()->json(['status' => true, 'message' => 'Member updated successfully.', 'data' => $member]);
    }

    /**
     * Remove the specified member from storage.
     */
    public function destroy($id)
    {
        $member = OrgIndependentMember::find($id);

        if (!$member) {
            return response()->json(['status' => false, 'message' => 'Member not found.'], 404);
        }

        // Delete the profile image if exists
        if ($member->image_path) {
            Storage::disk('public')->delete($member->image_path);
        }

        $member->delete();

        return response()->json(['status' => true, 'message' => 'Member deleted successfully.']);
    }
}
