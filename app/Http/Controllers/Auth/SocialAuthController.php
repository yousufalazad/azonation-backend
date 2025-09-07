<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\StoragePackage;
use App\Models\ManagementPackage;
use App\Models\Referral;
use App\Models\ReferralCode;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        // If you need to set a custom redirect URL, update config/services.php for 'google.redirect'
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        // If you're truly API-only/no sessions, use ->user()
        $googleUser = Socialite::driver('google')->user();

        // Google profile
        $googleId    = $googleUser->getId();
        $email       = $googleUser->getEmail();
        $name        = $googleUser->getName();     // "Full Name"
        $avatar      = $googleUser->getAvatar();
        $verified    = data_get($googleUser->user, 'email_verified') === true;

        // 1) Try by google_id
        $user = User::where('google_id', $googleId)->first();

        // 2) Or fallback by email (link account if matches)
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
            if ($user && !$user->google_id) {
                // Link Google to existing user
                $user->google_id     = $googleId;
                $user->oauth_provider = 'google';
                $user->google_avatar = $avatar;
                if ($verified && !$user->email_verified_at) {
                    $user->email_verified_at = now();
                }
                $user->save();
            }
        }

        // 3) If brand new, create minimal record (no custom fields yet)
        if (!$user) {
            $user = User::create([
                'email'              => $email,
                // Store name into first/last OR leave for completion step
                'first_name'         => null,
                'last_name'          => null,
                'org_name'           => null,
                'type'               => 'pending', // force completion form to decide
                'password'           => null, // OAuth user; optional
                'google_id'          => $googleId,
                'oauth_provider'     => 'google',
                'google_avatar'      => $avatar,
                'email_verified_at'  => $verified ? now() : null,
            ]);
        }

        // 4) Do we already have everything required?
        $needsCompletion = !$user->registration_completed
            || !$user->type
            || ($user->type === 'individual'  && (!$user->first_name || !$user->last_name))
            || ($user->type === 'organisation' && !$user->org_name)
            || !$user->userCountry()->exists();

        if ($needsCompletion) {
            // Create a short-lived token to call /api/oauth/google/complete
            $tempToken = $user->createToken('oauth-complete', ['oauth:complete'])->plainTextToken;

            // Frontend route should read these query params and render a completion form
            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
            return redirect()->away($frontendUrl . '/oauth/complete?token=' . urlencode($tempToken) . '&email=' . urlencode($user->email));
        }

        // 5) All good → issue normal login token and bounce to app
        $accessToken = $user->createToken('Personal Access Token')->plainTextToken;
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        return redirect()->away($frontendUrl . '/oauth/signed-in?accessToken=' . urlencode($accessToken));
    }

    public function completeProfile(Request $request)
    {
        // Auth via the short-lived token we issued in the callback
        $user = $request->user(); // Sanctum will bind user by Bearer token

        $validated = $request->validate([
            'type'        => 'required|string|in:individual,organisation',
            'country_id'  => 'required|numeric|max:999',
            // individual:
            'first_name'  => 'nullable|string|max:50',
            'last_name'   => 'nullable|string|max:50',
            // organisation:
            'org_name'    => 'nullable|string|max:100',
            // optional
            'referral'        => 'nullable|string|max:100',
            'referral_source' => 'nullable|string|max:50',
        ]);

        // Enforce your “either org OR individual” rule:
        if ($validated['type'] === 'individual') {
            if (!filled($validated['first_name']) || !filled($validated['last_name'])) {
                return response()->json(['message' => 'First name and Last name are required for individual.'], 422);
            }
            $user->first_name = $validated['first_name'];
            $user->last_name  = $validated['last_name'];
            $user->org_name   = null;
        } else {
            if (!filled($validated['org_name'])) {
                return response()->json(['message' => 'Organisation name is required for organisation.'], 422);
            }
            $user->org_name   = $validated['org_name'];
            $user->first_name = null;
            $user->last_name  = null;
        }

        $user->type = $validated['type'];
        $user->google_avatar = $user->google_avatar ?: null;

        // Save/update country relation
        $user->userCountry()->updateOrCreate(
            ['user_id' => $user->id],
            ['country_id' => $validated['country_id'], 'is_active' => 1]
        );

        // Your existing “organisation bootstrap” logic:
        if ($user->type === 'organisation') {
            $management_package_id = \App\Models\ManagementPackage::value('id');
            $user->managementSubscription()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'management_package_id' => $management_package_id,
                    'start_date' => now(),
                    'subscription_status' => 'active',
                    'is_active' => 1,
                ]
            );

            $storage_package_id = \App\Models\StoragePackage::value('id');
            $user->storageSubscription()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'storage_package_id' => $storage_package_id,
                    'start_date' => now(),
                    'subscription_status' => 'active',
                    'is_active' => 1,
                ]
            );

            $user->accountFund()->firstOrCreate(
                ['user_id' => $user->id, 'name' => 'General Fund'],
                ['is_active' => 1]
            );
        }

        // Optional: referrals
        if (!empty($validated['referral_source'])) {
            $refCode = null;
            $referrerId = null;
            if (!empty($validated['referral'])) {
                $refCode = ReferralCode::where('code', $validated['referral'])->where('status', 'active')->first();
                if ($refCode && $refCode->user_id !== $user->id) {
                    $referrerId = $refCode->user_id;
                    $refCode->increment('times_used');
                }
            }
            Referral::create([
                'referral_code_id' => $refCode?->id,
                'referrer_id'      => $referrerId,
                'referred_user_id' => $user->id,
                'email'            => $user->email,
                'ip_address'       => $request->ip(),
                'user_agent'       => $request->userAgent(),
                'signup_completed' => true,
                'reward_given'     => false,
                'referral_source'  => $validated['referral_source'],
            ]);
        }

        $user->registration_completed = true;
        $user->save();

        // Issue your normal app token and return payload like your login()
        $accessToken = $user->createToken('Personal Access Token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Profile completed',
            'data' => [
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
                'accessToken' => $accessToken,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}
