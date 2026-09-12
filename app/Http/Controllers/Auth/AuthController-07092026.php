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

use Spatie\Permission\Models\Role;
use App\Models\OrgMemberRoleTitle;
use App\Models\OrgRoleTitle;


use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    // 🔐 LOGIN
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_token' => 'sometimes|boolean',
        ]);

        $email = strtolower($validated['email']);
        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->error('Invalid credentials.');
        }

        if (is_null($user->password)) {
            return $this->error(
                'This account uses Google sign-in. Continue with Google, or set a password first.'
            );
        }

        $remember = (bool) ($validated['remember_token'] ?? false);

        if (!Auth::attempt(['email' => $email, 'password' => $validated['password']], $remember)) {
            return $this->error('Invalid credentials.');
        }

        $user = $request->user();

        if (isset($user->registration_completed) && !$user->registration_completed) {
            Auth::logout();
            return $this->error('Please complete your profile first.');
        }

        $token = $user->createToken('Personal Access Token')->plainTextToken;


        // 🔥 NEW: org-wise roles + permissions
        $orgAccess = $this->getOrgAccess($user);

        return $this->success(
            message: 'Successfully logged in',
            data: [
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

                // ❌ OLD remove (global roles/permissions)
                // 'roles' => $user->roles->pluck('name'),
                // 'permissions' => $permissions,

                // ✅ NEW org ভিত্তিক data
                'org_access'    => $orgAccess,
            ]
        );
    }

    // 🔥 STEP 1: permission merge (multi role → single list)
    private function getPermissionsByOrg($userId, $orgId)
    {
        return DB::table('model_has_roles')
            ->join('role_has_permissions', 'model_has_roles.role_id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('model_has_roles.model_id', $userId)
            ->where('model_has_roles.org_type_user_id', $orgId)
            ->pluck('permissions.name')
            ->unique() // duplicate remove
            ->values();
    }
    public function switchOrg(Request $request)
    {
        $user = $request->user();
        $orgId = $request->header('X-Org-Id');

        $orgAccess = $this->getOrgAccess($user);

        $org = collect($orgAccess)->firstWhere('org_type_user_id', $orgId);

        if (!$org) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid organization'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data' => $org
        ]);
    }
    // 🔥 STEP 2: org-wise roles + permissions   private
    public function getOrgAccess($user)
    {
        // all org list
        $orgs = DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->select('org_type_user_id')
            ->distinct()
            ->get();

        $data = [];

        foreach ($orgs as $org) {

            // roles
            $roles = DB::table('roles')
                ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_id', $user->id)
                ->where('model_has_roles.org_type_user_id', $org->org_type_user_id)
                ->pluck('roles.name')
                ->unique()
                ->values();

            // permissions (merged from all roles)
            $permissions = $this->getPermissionsByOrg(
                $user->id,
                $org->org_type_user_id
            );

            $data[] = [
                'org_type_user_id' => $org->org_type_user_id,
                'roles' => $roles,
                'permissions' => $permissions
            ];
        }

        return $data;
    }

    public function X_register(Request $request)
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
            'registration_completed' => true,
            'password' => Hash::make($request->password),
        ]);

        $user->userLanguage()->create([
            'user_id' => $user->id,
            'language_id' => 1, // Default language_id set to 1
            'is_active' => 1,
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
            $user->fund()->create([
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
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'org_name' => 'nullable|string|max:100',
            'subscription_id' => 'nullable',
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
            'registration_completed' => true,
            'password' => Hash::make($request->password),
        ]);

        // $user->userLanguage()->create([
        //     'user_id' => $user->id,
        //     'language_id' => 1, // Default language_id set to 1
        //     'is_active' => 1,
        // ]);

        if ($request->country_id) {
            $user->userCountry()->create([
                'user_id' => $user->id,
                'country_id' => $request->country_id,
                'is_active' => 1,
            ]);
        }

        $management_package_id = ManagementPackage::value('id');
        // $management_package_id = $request->subscription_id ?? ManagementPackage::value('id'); // Use provided subscription_id or default to first id
        if ($request->type == 'organisation') {

            $isNewUser = true;

            $this->assignUserRoles(
                $user->id,
                ['free_trial_org'],
                $user->id,
                'admin',
                $isNewUser
            );

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
            $user->fund()->create([
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

    private function assignUserRoles(
        $userId,
        array $roles,
        $orgTypeUserId,
        $orgRoleTitle = null,
        $isNewUser = false
    ) {
        // DB::beginTransaction();

        // try {
        $user = User::findOrFail($userId);

        // ✅ IMPORTANT: roles are now org-based
        $roleModels = Role::whereIn('name', $roles)
            // ->where('org_type_user_id', $orgTypeUserId)
            ->where('guard_name', 'web')
            ->get();

        // Existing user cleanup only
        // if (!$isNewUser) {

        //     $existingRoleIds = DB::table('model_has_roles')
        //         ->where('model_type', User::class)
        //         ->where('model_id', $user->id)
        //         ->pluck('role_id')
        //         ->toArray();

        //     $newRoleIds = $roleModels->pluck('id')->toArray();

        //     $toDelete = array_diff($existingRoleIds, $newRoleIds);

        //     if (!empty($toDelete)) {
        //         DB::table('model_has_roles')
        //             ->whereIn('role_id', $toDelete)
        //             ->where('model_type', User::class)
        //             ->where('model_id', $user->id)
        //             ->delete();
        //     }
        // }

        // Insert roles
        foreach ($roleModels as $role) {
            DB::table('model_has_roles')->updateOrInsert(
                [
                    'role_id' => $role->id,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ],
                [
                    'org_type_user_id' => $orgTypeUserId
                ]
            );
        }
        // table org_role_titles insert/update
        if ($orgRoleTitle) {
            $orgRoleTitleData = OrgRoleTitle::updateOrCreate(
                [
                    'org_type_user_id' => $orgTypeUserId,
                    'name' => $orgRoleTitle,
                ],

            );
        }
        // Org role title (IMPORTANT: should be org-based unique)
        if ($orgRoleTitleData) {
            OrgMemberRoleTitle::updateOrCreate(
                [
                    'org_type_user_id' => $orgTypeUserId,
                    'individual_type_user_id' => $userId,
                ],
                [
                    'org_role_title_id' => $orgRoleTitleData['id'],
                ]
            );
        }

        //     DB::commit();

        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     throw $e;
        // }
    }
    public function assignRoles(Request $request, $userId)
    {
        // dd($request->all());exit;
        $request->validate([
            'roles' => 'nullable|array',
            'org_type_user_id' => 'required|integer',
            'org_role_title_id' => 'nullable|integer'
        ]);

        DB::beginTransaction();

        try {
            $user = User::findOrFail($userId);

            $roles = collect($request->roles ?? [])
                ->map(fn($r) => trim($r))
                ->toArray();

            $roleModels = Role::whereIn('name', $roles)
                ->where('guard_name', 'web')
                ->get();

            // Sync roles manually with org_type_user_id
            $existingRoleIds = DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->where('model_id', $user->id)
                ->pluck('role_id')
                ->toArray();

            $newRoleIds = $roleModels->pluck('id')->toArray();

            // Remove old roles not in new
            $toDelete = array_diff($existingRoleIds, $newRoleIds);
            if ($toDelete) {
                DB::table('model_has_roles')
                    ->whereIn('role_id', $toDelete)
                    ->where('model_type', User::class)
                    ->where('model_id', $user->id)
                    ->delete();
            }

            // Insert/update new roles with org_type_user_id
            foreach ($roleModels as $role) {
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id' => $role->id,
                        'model_type' => User::class,
                        'model_id' => $user->id,
                    ],
                    [
                        'org_type_user_id' => $request->org_type_user_id
                    ]
                );
            }

            // Save org member title
            if ($request->org_role_title_id) {
                OrgMemberRoleTitle::updateOrCreate(
                    [
                        'org_type_user_id' => $request->org_type_user_id,
                        'individual_type_user_id' => $userId,
                    ],
                    [
                        'org_role_title_id' => $request->org_role_title_id
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Roles assigned successfully',
                'roles' => $roleModels->pluck('name')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
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
