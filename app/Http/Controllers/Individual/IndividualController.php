<?php

namespace App\Http\Controllers\Individual;
use App\Http\Controllers\Controller;
use App\Models\Individual;
use App\Models\OrgMemberList;
use App\Models\ProfileImage;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; // Import Carbon for timestamp

class IndividualController extends Controller
{

    protected function success($message, $data = [], $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function getIndividualUser()
    {
        $individualUsers = User::where('type', 'individual')->get();
        return response()->json([
            'status' => true,
            'data' => $individualUsers
        ]);
    }

    // getProfilePicture
    public function getProfileImage($individualId)
    {
        $profileImage = ProfileImage::where('individual_id', $individualId)->orderBy('id', 'desc')->first();
        $imageUrl = $profileImage ? Storage::url($profileImage->image) : null;

        return response()->json([
            'status' => true,
            'data' => ['image' => $imageUrl]
        ]);
    }

    // getProfileImage update 
    public function updateProfileImage(Request $request, $individualId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20048',
        ]);

        $individual = Individual::find($individualId);
        if (!$individual) {
            return response()->json(['status' => false, 'message' => 'Individual account not found'], 404);
        }

        $image = $request->file('image');
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $timestamp = Carbon::now()->format('YmdHis');
        $newFileName = $timestamp . '_' . $originalName . '.' . $extension;

        $path = $image->storeAs('individual/profileimages', $newFileName, 'public');

        $profileImage = ProfileImage::where('individual_id', $individualId)->orderBy('id', 'desc')->first();
        if ($profileImage) {
            //ekhane delete korte hobe save korar agee
            $profileImage->individual_id = $individualId;
            $profileImage->image = $path;
            $profileImage->save();
        } else {
            // Save the profile image path to profile_images table
            $profileImage = new ProfileImage();
            $profileImage->individual_id = $individualId;
            $profileImage->image = $path;
            $profileImage->save();
        }
        $imageUrl = Storage::url($path);
        return response()->json(['status' => true, 'data' => ['image' => $imageUrl]]);
    }

    public function getOrganisationByIndividualId($individualId)
    {
        $organisations = OrgMemberList::where('individual_id', $individualId)
            ->with('connectedorg')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $organisations,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $individualData = Individual::where('user_id', $id)->first();

        if ($individualData) {
            return response()->json(['status' => true, 'data' => $individualData]);
        } else {
            return response()->json(['status' => false, 'message' => 'Individual data not found']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Individual $individual)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Individual $individual)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Individual $individual)
    {
        //
    }
}
