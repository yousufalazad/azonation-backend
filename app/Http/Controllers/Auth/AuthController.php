<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SuperAdminUserRegisteredMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserCountry;
use App\Models\ManagementSubscription;
use App\Models\StorageSubscription;
use App\Models\Fund;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\IndividualUserRegisteredMail;
use App\Mail\OrgUserRegisteredMail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'country_id' => 'required|numeric|max:999',
            'type' => 'required|string|max:12',
            'password' => 'required|string|min:8',
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'type' => $request->type,
            'password' => Hash::make($request->password),
        ]);

        if ($request->country_id) {
            $user->userCountry()->create([
                'user_id' => $user->id,
                'country_id' => $request->country_id,
                'is_active' => 1,
            ]);
        }

        if ($request->type == 'organisation') {
            $user->managementSubscription()->create([
                'user_id' => $user->user_id,
                'management_package_id' => 1,
                'start_date' => now(),
                'subscription_status' => 'active',
                'is_active' => 1,
                'created_at' => now(),
            ]);
            $user->storageSubscription()->create([
                'user_id' => $user->user_id,
                'storage_package_id' => 1,
                'start_date' => now(),
                'subscription_status' => 'active',
                'is_active' => 1,
                'created_at' => now(),
            ]);
            $user->Fund->create([
                'user_id' => $user->id,
                'fund_name' => 'Default Fund',
                'is_active' => 1,
            ]);
        }

        //send email to user based on type
        if ($user->type == 'individual') {
            Mail::to($user->email)->queue(new IndividualUserRegisteredMail($user));
        } elseif ($user->type == 'organisation') {
            Mail::to($user->email)->queue(new OrgUserRegisteredMail($user));
        } elseif ($user->type == 'superadmin') {
            Mail::to($user->email)->queue(new SuperAdminUserRegisteredMail($user));
        }

        // $this->sendEmail($user);
        return response()->json([
            'status' => true,
            'message' => 'Registration successful',
            'data' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_token' => 'required|boolean',
        ]);
        $credentials = request(['email', 'password']);
        $remember_token = $request->remember_token;
        if (!Auth::attempt($credentials, $remember_token)) {
            return $this->error('Unauthorized user');
        }
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;
        return $this->success(message: 'Successfully logged in', data: [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => $user->type,
            'azon_id' => $user->azon_id,
            'username' => $user->username,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'accessToken' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    // public function sendEmail($user)
    // {
    //     if ($user->type == 'individual') {
    //         Mail::to($user->email)->queue(new IndividualUserRegisteredMail($user));
    //     } elseif ($user->type == 'organisation') {
    //         Mail::to($user->email)->queue(new OrgUserRegisteredMail($user));
    //     } elseif ($user->type == 'superadmin') {
    //         Mail::to($user->email)->queue(new SuperAdminUserRegisteredMail($user));
    //     }
    // }
    protected function success($message, $data = [], $status = 200)
    {
        return response()->json(data: [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], status: $status);
    }
    protected function error($message, $errors = [], $status = 422)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
    public function verify($uuid)
    {
        $user = User::where('verification_token', $uuid)->first();
        if (!$user) {
            return redirect('/')->with('error', 'Invalid verification link.');
        }
        $user->email_verified_at = Carbon::now();
        $user->verification_token = null;
        $user->save();
        return redirect('/')->with('success', 'Your email has been verified!');
    }
    public function nameUpdate(Request $request, $userId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);
        try {
            $user = User::findOrFail($userId);
            $user->name = $validated['name'];
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'Name updated successfully',
                'data' => $user
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the name',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function usernameUpdate(Request $request, $userId)
    {
        $request->validate([
            'username' => 'required|string|max:30|unique:users,username,' . $userId,
        ]);
        $user = User::findOrFail($userId);
        $user->username = $request->username;
        $user->save();
        return response()->json([
            'status' => true,
            'message' => 'Username updated successfully',
            'data' => $user
        ]);
    }
    public function userEmailUpdate(Request $request, $userId)
    {
        $request->validate([
            'email' => 'required|string|max:100',
        ]);
        $user = User::where('id', $userId)->first();
        $user->email = $request->email;
        $user->save();
        return response()->json([
            'status' => true,
            'message' => 'Email updated successfully',
            'data' => $user
        ]);
    }
    public function updatePassword(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|confirmed|string|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $user = User::findOrFail($userId);
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'The current password is incorrect.',
                ], 422);
            }
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the password: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
