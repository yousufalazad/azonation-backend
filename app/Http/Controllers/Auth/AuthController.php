<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SuperAdminUserRegisteredMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\StoragePackage;
use App\Models\ManagementPackage;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\ReferralCode;
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
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'org_name' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'country_id' => 'required|numeric|max:999',
            'type' => 'required|string|max:12|in:individual,organisation',
            'password' => 'required|string|min:8',
            'referral' => 'nullable|string|max:100',
            'referral_source' => 'nullable|string|max:50',
        ]);
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'org_name' => $request->org_name,
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

        $management_package_id = ManagementPackage::value('id'); // gets first id directly or null
        if ($request->type == 'organisation') {
            $user->managementSubscription()->create([
                'user_id' => $user->user_id,
                'management_package_id' => $management_package_id,
                'start_date' => now(),
                'subscription_status' => 'active',
                'is_active' => 1,
                'created_at' => now(),
            ]);

            $storage_package_id = StoragePackage::value('id'); // gets first id directly or null
            $user->storageSubscription()->create([
                'user_id' => $user->user_id,
                'storage_package_id' => $storage_package_id,
                'start_date' => now(),
                'subscription_status' => 'active',
                'is_active' => 1,
                'created_at' => now(),
            ]);
            $user->accountFund()->create([
                'user_id' => $user->user_id,
                'name' => 'General Fund',
                'is_active' => 1,
            ]);

            $refCode = null;
            $referrerId = null;

            // Check if referral code exists
            if ($request->referral) {
                $refCode = ReferralCode::where('code', $request->referral)->where('status', 'active')->first();
                if ($refCode && $refCode->user_id !== $user->id) {
                    $referrerId = $refCode->user_id;
                    $refCode->increment('times_used');
                }
            }

            // Save referral record regardless of referral code validity
            Referral::create([
                'referral_code_id' => $refCode?->id,
                'referrer_id' => $referrerId,
                'referred_user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'signup_completed' => true,
                'reward_given' => false,
                'referral_source' => $request->referral_source ?? null,
            ]);
        }

        // Send email to user based on type
        switch ($user->type) {
            case 'individual':
                Mail::to($user->email)->queue(new IndividualUserRegisteredMail($user));
                break;
            case 'organisation':
                Mail::to($user->email)->queue(new OrgUserRegisteredMail($user));
                break;
            case 'superadmin':
                Mail::to($user->email)->queue(new SuperAdminUserRegisteredMail($user));
                break;
        }


        // $this->sendEmail($user);
        return response()->json([
            'status' => true,
            'message' => 'Registration successful',
            'data' => $user
        ]);
    }

    public function Xremove_login(Request $request)
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
            'first_name' => $user->first_name ? $user->first_name : null,
            'last_name' => $user->last_name ? $user->last_name : null,
            'org_name' => $user->org_name ? $user->org_name : "issue here",
            'country_name' => $user->userCountry ? $user->userCountry->country->name : null,
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

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_token' => 'required|boolean',
        ]);

        // 1) Find the user first so we can handle OAuth-only accounts nicely
        $user = \App\Models\User::where('email', $validated['email'])->first();

        if (!$user) {
            return $this->error('Invalid credentials.');
        }

        // 2) If the account has no local password, guide them to Google or to set a password
        if (is_null($user->password)) {
            return $this->error(
                'This account uses Google sign-in. Continue with Google, or set a password from your profile first.'
            );
        }

        // 3) Attempt normal email/password login
        $remember = (bool) $validated['remember_token'];
        if (!\Illuminate\Support\Facades\Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $remember)) {
            return $this->error('Invalid credentials.');
        }

        // Refresh the authenticated user instance
        $user = $request->user();

        // (Optional) If you use a registration gate, block incomplete profiles
        if (isset($user->registration_completed) && !$user->registration_completed) {
            \Illuminate\Support\Facades\Auth::logout();
            return $this->error('Please complete your profile via Google sign-in before logging in.');
        }

        // 4) Issue token and return payload (kept same shape as before)
        $token = $user->createToken('Personal Access Token')->plainTextToken;

        return $this->success(message: 'Successfully logged in', data: [
            'id'            => $user->id,
            'first_name'    => $user->first_name ?: null,
            'last_name'     => $user->last_name ?: null,
            'org_name'      => $user->org_name ?: null,
            'country_name'  => $user->userCountry ? $user->userCountry->country->name : null,
            'email'         => $user->email,
            'type'          => $user->type,
            'azon_id'       => $user->azon_id,
            'username'      => $user->username,
            'created_at'    => $user->created_at,
            'updated_at'    => $user->updated_at,
            'accessToken'   => $token,
            'token_type'    => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return $this->success('OK', [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'org_name' => $user->org_name,
            'country_name' => $user->userCountry?->country?->name,
            'email' => $user->email,
            'type' => $user->type,
            'azon_id' => $user->azon_id,
            'username' => $user->username,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
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

        $referral = Referral::where('referred_user_id', $user->id)->first();

        if ($referral && ! $referral->reward_given) {
            // Create reward
            ReferralReward::create([
                'referral_id' => $referral->id,
                'user_id' => $referral->referrer_id,
                'reward_type' => $referral->referralCode->reward_type ?? 'credit',
                'amount' => $referral->referralCode->reward_value ?? 10,
                'status' => 'approved',
                'rewarded_at' => now(),
                'notes' => 'Referral reward granted after user verification',
            ]);

            $referral->update([
                'reward_given' => true,
                'rewarded_at' => now(),
            ]);
        }
        return redirect('/')->with('success', 'Your email has been verified!');
    }
    public function firstLastNameUpdate(Request $request, $userId)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
        ]);
        try {
            $user = User::findOrFail($userId);
            $user->first_name = $validated['first_name'];
            $user->last_name = $validated['last_name'];
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'First Name updated successfully',
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
    public function lastNameUpdate(Request $request, $userId)
    {
        $validated = $request->validate([
            'last_name' => 'required|string|max:100',
        ]);
        try {
            $user = User::findOrFail($userId);
            $user->last_name = $validated['last_name'];
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'Last Name updated successfully',
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
                'message' => 'An error occurred while updating the last_name',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function nameUpdate(Request $request, $userId)
    {
        $validated = $request->validate([
            'org_name' => 'required|string|max:100',
        ]);
        try {
            $user = User::findOrFail($userId);
            $user->org_name = $validated['org_name'];
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'Org Name updated successfully',
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
        try {
            $user = User::findOrFail($userId);

            // Build validation rules dynamically:
            $rules = [
                'password' => 'required|string|min:8|confirmed', // needs password_confirmation
            ];

            // If the user already has a local password, require the old one.
            if (filled($user->password)) {
                $rules['old_password'] = 'required';
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // If a password exists, verify old_password and ensure new != old
            if (filled($user->password)) {
                if (!Hash::check($request->old_password, $user->password)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'The current password is incorrect.',
                    ], 422);
                }
                if (Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'New password must be different from the current password.',
                    ], 422);
                }
            }

            // Set/replace password
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => filled($user->password)
                    ? 'Password updated successfully.'
                    : 'Password set successfully.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the password: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function X_logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function logout(Request $request)
{
    try {
        // Revoke ALL personal access tokens (safest)
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }

        // Log out of the session guard (clears authentication)
        Auth::guard('web')->logout();

        // Invalidate & regenerate session (CSRF token etc.)
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Explicitly forget cookies that can keep you logged in
        $cookiesToForget = [
            config('session.cookie', 'laravel_session'),
            Auth::getRecallerName(), // remember_web_xxx
            'XSRF-TOKEN',            // optional, nice to reset
        ];

        $response = response()->json(['message' => 'Logged out successfully']);

        foreach ($cookiesToForget as $name) {
            $response->headers->setCookie(
                Cookie::forget($name, config('session.path', '/'), config('session.domain'))
            );
        }

        return $response;

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Logout failed',
            'error'   => $e->getMessage(),
        ], 500);
    }
}
}
