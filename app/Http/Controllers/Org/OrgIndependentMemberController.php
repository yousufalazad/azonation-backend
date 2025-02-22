<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\IndependentMemberImage;
use App\Models\OrgIndependentMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; // Import Carbon for timestamp
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class OrgIndependentMemberController extends Controller
{
    /**
     * Display a listing of the members.
     */
    // public function ___index()
    // {
    //     $members = OrgIndependentMember::all();
    //     // Map the members to include the image URL
    //     $members = $members->map(function ($member) {
    //         $member->image_url = $member->image_path ? Storage::url($member->image_path) : null;
    //         return $member;
    //     });

    //     return response()->json(['status' => true, 'data' => $members]);
    // }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $members = OrgIndependentMember::where('user_id', $userId)->get();
        //dd($members);

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
        dd($request);
        $validatedData['user_id'] = $request->user()->id;
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:org_independent_members,email',
            'mobile' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'note' => 'nullable|string',
            'is_active' => 'required|boolean',
            //'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Image validation for each file
        ]);

        $member = new OrgIndependentMember();
        $member->user_id = $request->user()->id;
        $member->name = $validatedData['name'];
        $member->email = $validatedData['email'];
        $member->mobile = $validatedData['mobile'];
        $member->address = $validatedData['address'];
        $member->note = $validatedData['note'];
        $member->is_active = $validatedData['is_active'];
        $member->save(); // Save the record in the database

        Log::info("Member updated");
        // Handle single image uploads
        if ($request->hasFile('image_path')) {
            Log::info("inside image upload");

            foreach ($request->file('image_path') as $image) {
                $imagePath = $image->storeAs(
                    'org/image/independent-member',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                Log::info('Image is now available');
                IndependentMemberImage::create([
                    'org_independent_member_id' => $member->id,
                    'file_path' => $imagePath, // Store the document path
                    'file_name' => $image->getClientOriginalName(), // Store the document name
                    'mime_type' => $image->getClientMimeType(), // Store the MIME type
                    'file_size' => $image->getSize(), // Store the size of the document
                    'is_public' => true, // Set the document as public
                    'is_active' => true, // Set the document as active
                ]);
            }
            Log::info('Done');
        }

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

        // Validate the input data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'email|unique:org_independent_members,email',
            'mobile' => 'string|max:15',
            'address' => 'nullable|string|max:500',
            'note' => 'nullable|string',
            'is_active' => 'required|boolean',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle the image upload if a file is provided
        if ($request->hasFile('image_path')) {
            // Delete the old image if it exists
            if ($member->image_path) {
                Storage::disk('public')->delete($member->image_path);
            }

            // Process the new image
            $image = $request->file('image_path');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $timestamp = Carbon::now()->format('YmdHis');
            $newFileName = $timestamp . '_' . $originalName . '.' . $extension;
            $path = $image->storeAs('org/independent_member', $newFileName, 'public');
            $validatedData['image_path'] = $path;
        }
        $validatedData['user_id'] = $request->user()->id;

        // Update the member record
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
