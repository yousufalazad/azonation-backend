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
use Illuminate\Support\Facades\Log;

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
            $founder->image_url = $founder->image ? url(Storage::url($founder->image->image_path ?? $founder->image->file_path)) : null;
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
                    ->orWhere('full_name', 'like', "%{$query}%")
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
            // 'user_id' => 'exists:users,id',
            'founder_user_id' => 'nullable',
            'full_name' => 'string|max:50',
            'designation' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:7168',
            'email' => 'nullable|email|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        $founder = Founder::create([
            //'user_id' => $request->user_id,
            'user_id' => Auth::id(),
            'founder_user_id' => $request->founder_user_id,
            'full_name' => $request->full_name,
            'designation' => $request->designation,
            'is_active' => $request->is_active??1,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'note' => $request->note,
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
            'full_name' => 'string|max:50',
            'designation' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:7168',
            'email' => 'nullable|email|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);
        try {
            $founder = Founder::findOrFail($id);
            $founder->full_name = $request->input('full_name');
            $founder->designation = $request->input('designation');
            $founder->is_active = $request->input('is_active');
            $founder->email = $request->input('email');
            $founder->mobile = $request->input('mobile');
            $founder->address = $request->input('address');
            $founder->note = $request->input('note');
            $founder->save();

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
    public function destroy($id)
    {
        try {
            $founder = Founder::findOrFail($id);
            $founder->delete();
            return response()->json([
                'status' => true,
                'message' => 'Founder Member deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting Founder Member: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
