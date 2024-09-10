<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\OrgProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; // Import Carbon for timestamp

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
        $logo = User::where('id', $userId)->orderBy('id', 'desc')->first();
        $imageUrl = $logo ? Storage::url($logo->image) : null;

        return response()->json([
            'status' => true,
            'data' => ['image' => $imageUrl]
        ]);
    }

    public function updateLogo(Request $request, $userId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20048',
        ]);

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Organization not found'], 404);
        }

        $image = $request->file('image');
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $timestamp = Carbon::now()->format('YmdHis');
        $newFileName = $timestamp . '_' . $originalName . '.' . $extension;

        $path = $image->storeAs('org/logos', $newFileName, 'public');

        $orgLogo = User::where('id', $userId)->orderBy('id', 'desc')->first();
        if ($orgLogo) {
            //ekhane delete korte hobe save korar agee
            $orgLogo->id = $userId;
            $orgLogo->image = $path;
            $orgLogo->save();
        } else {
            // Save the logo path to users table
            $orgLogo = new User();
            $orgLogo->id = $userId;
            $orgLogo->image = $path;
            $orgLogo->save();
        }
        $imageUrl = Storage::url($path);
        return response()->json(['status' => true, 'data' => ['image' => $imageUrl]]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index($userId)
    {
        $orgProfileData = OrgProfile::where('user_id', $userId)->first();
        
        if ($orgProfileData) {
            return response()->json(['status' => true, 'data' => $orgProfileData]);
        } else {
            return response()->json(['status' => false, 'message' => 'Organisation not found']);
        }
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
    public function show(OrgProfile $orgProfile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgProfile $orgProfile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $userId): JsonResponse
    {
        // Validate the request data
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
            // Find the organization profile by user ID
            $orgProfile = OrgProfile::firstOrNew(['user_id' => $userId]);

            // Update or create new profile based on existence
            $orgProfile->fill($validatedData);
            $orgProfile->save();

            $message = $orgProfile->wasRecentlyCreated
                ? 'Org profile created successfully.'
                : 'Org profile updated successfully.';

            // Return a JSON response
            return response()->json([
                'status'  => true,
                'data'    => $orgProfile,
                'message' => $message,
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error("Failed to update or create OrgProfile for user ID {$userId}: " . $e->getMessage());

            // Return a generic error response
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgProfile $orgProfile)
    {
        //
    }
}
