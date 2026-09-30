<?php

namespace App\Http\Controllers\Common;

use App\Http\Concerns\OwnsPersonalRecords;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\UserCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserCountryController extends Controller
{
    use OwnsPersonalRecords;

    public function getUser()
    {
        // Every account on the platform: Super Admin only
        if (Auth::user()?->type !== 'superadmin') {
            return response()->json(['status' => false, 'message' => 'Not allowed.'], 403);
        }
        $users = User::whereNull('deleted_at')->orderBy('id')->get(['id', 'type', 'email', 'org_name', 'first_name', 'last_name'])
            ->map(fn ($u) => ['id' => $u->id, 'type' => $u->type, 'name' => ($u->org_name ?: trim("{$u->first_name} {$u->last_name}")) ?: $u->email, 'email' => $u->email]);
        return response()->json(['status' => true, 'data' => $users], 200);
    }
    public function index()
    {
        // Super Admins see every account's country; anyone else only their own
        $usersCountry = UserCountry::select('user_countries.*', 'countries.name as country_name', 'users.type as user_type', 'users.email as user_email',
                DB::raw("COALESCE(NULLIF(users.org_name, ''), NULLIF(TRIM(CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))), ''), users.email) as user_name"))
            ->leftJoin('users', 'user_countries.user_id', '=', 'users.id')
            ->leftJoin('countries', 'user_countries.country_id', '=', 'countries.id')
            ->when(Auth::user()?->type !== 'superadmin', fn ($q) => $q->where('user_countries.user_id', Auth::id()))
            ->orderBy('user_name')
            ->get();
        return response()->json(['status' => true, 'data' => $usersCountry], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        // Whose record: always the signed-in person (Super Admins may name a user)
        $request->merge(['user_id' => $this->personalOwnerId($request)]);
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'user_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('User Country data: ', ['country_id' => $request->country_id, 'user_id' => $request->user_id]);
            $dialingCode = UserCountry::create([
                'country_id' => $request->country_id,
                'user_id' => $request->user_id,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'User Country created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Country: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create User Country.'], 500);
        }
    }
    public function show(UserCountry $userCountry) {}
    public function edit(UserCountry $userCountry) {}
    public function update(Request $request, $id)
    {
        // Whose record: always the signed-in person (Super Admins may name a user)
        $request->merge(['user_id' => $this->personalOwnerId($request)]);
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'user_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $dialingCode = $this->mine(UserCountry::class)->find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'User Country not found.'], 404);
        }
        $dialingCode->update([
            'country_id' => $request->country_id,
            'user_id' => $request->user_id,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'User Country updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $dialingCode = $this->mine(UserCountry::class)->find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'User Country not found.'], 404);
        }
        $dialingCode->delete();
        return response()->json(['status' => true, 'message' => 'User Country deleted successfully.'], 200);
    }
}
