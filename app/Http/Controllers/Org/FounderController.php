<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\Founder;
use App\Models\FounderProfileImage;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class FounderController extends Controller
{

    public function X_index(Request $request)
    {
        $user_id = Auth::id();
        $founders = Founder::where('user_id', $user_id)
            ->with(['image'])->get();

        if ($founders->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Founders not found'
            ], 404);
        }

        $founders = $founders->map(function ($founder) {
            $founder->image_url = $founder->image && $founder->image->file_path
                ? url(Storage::url($founder->image->file_path))
                : null;
            unset($founder->image);
            return $founder;
        });

        // dd($founders);
        return response()->json([
            'status' => true,
            'data' => $founders
        ], 200);
    }


    public function index(Request $request)
    {
        $user_id = Auth::id();
        $founders = Founder::where('user_id', $user_id)
            ->with(['founders'])
            ->get();

        $founders = $founders->map(function ($founder) {
            $founder->image_url = $founder->image ? url(Storage::url($founder->image->image_path??$founder->image->file_path)):null;
            return $founder;
        });

        // dd($founders);
        return response()->json([
            'status' => true,
            'data' => $founders
        ], 200);
    }



    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = User::where('type', 'individual')
            ->where(function ($q) use ($query) {
                $q->where('azon_id', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhereRaw("CONCAT(dialing_codes.dialing_code, phone_numbers.phone_number) LIKE ?", ["%{$query}%"]);
            })
            ->leftJoin('addresses', 'addresses.user_id', '=', 'users.id')
            // ->leftJoin('countries', 'countries.id', '=', 'addresses.country_id')
            ->leftJoin('phone_numbers', 'phone_numbers.user_id', '=', 'users.id')
            ->leftJoin('dialing_codes', 'dialing_codes.id', '=', 'phone_numbers.dialing_code_id')
            ->select(
                'users.*',
                'addresses.city',
                // 'countries.country_name',
                'dialing_codes.dialing_code',
                'phone_numbers.phone_number'
            )
            ->get();
        return response()->json([
            'status' => true,
            'data' => $results
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'exists:users,id',
            'founder_user_id' => 'nullable',
            'name' => 'string|max:50',
            'designation' => 'string|max:50',
            'is_active' => 'boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:7168',
        ]);

        $founder = Founder::create([
            'user_id' => $request->user_id,
            'founder_user_id' => $request->founder_user_id,
            'name' => $request->name,
            'designation' => $request->designation,
            'is_active' => $request->is_active,
        ]);
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imagePath = $image->storeAs(
                'org/founder-profile/image/',
                Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                'public'
            );
            FounderProfileImage::create([
                'founder_id' => $founder->id,
                'file_path' => $imagePath,
                'file_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getClientMimeType(),
                'file_size' => $image->getSize(),
                'is_public' => true,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Founder added successfully',
            'data' => $founder,
        ]);
    }

    public function create() {}
    public function show(Founder $founder) {}
    public function edit(Founder $founder) {}
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:50',
            'designation' => 'string|max:50',
            'is_active' => 'boolean',
        ]);
        try {
            $founder = Founder::findOrFail($id);
            $founder->name = $request->input('name');
            $founder->designation = $request->input('designation');
            $founder->is_active = $request->input('is_active');
            $founder->save();
            return response()->json([
                'status' => true,
                'message' => 'Founder designation updated successfully.',
                'data' => $founder
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update founder designation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy(Founder $founder) {}
}
