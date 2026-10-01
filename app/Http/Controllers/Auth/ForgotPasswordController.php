<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Password reset in three steps:
 *   1. sendResetCode   - emails a 6-digit code (valid 10 minutes)
 *   2. verifyResetCode - checks the code and returns a one-time reset token
 *   3. resetPassword   - needs the email AND that token to set the new password
 *
 * Only a hash of the code/token is stored, wrong codes are limited to
 * MAX_ATTEMPTS per code, and responses never say whether an email exists
 * or include the user record.
 */
class ForgotPasswordController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const CODE_MINUTES = 10;
    private const TOKEN_MINUTES = 15;

    // Stored value for a code or token ("code:" and "token:" can never match each other)
    private function digest(string $kind, string $value): string
    {
        return hash('sha256', $kind . ':' . $value);
    }

    private function attemptsKey(string $email): string
    {
        return 'password-reset-attempts:' . strtolower($email);
    }

    // Step 1: Send reset code
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $code = (string) random_int(100000, 999999); // 6 digits, cryptographically random
            $user->reset_code = $this->digest('code', $code);
            $user->reset_code_expires_at = Carbon::now()->addMinutes(self::CODE_MINUTES);
            $user->save();
            Cache::forget($this->attemptsKey($user->email));

            Mail::to($user->email)->queue(new PasswordResetCodeMail($code));
        }

        // Same answer whether or not the account exists
        return response()->json([
            'status' => true,
            'message' => 'If an account exists for this email, we have sent a reset code.',
        ]);
    }

    // Step 2: Verify reset code, return a one-time reset token
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|max:10',
        ]);

        $invalid = response()->json(['status' => false, 'message' => 'The code is wrong or has expired. Request a new code.'], 422);

        $user = User::where('email', $request->email)->first();
        if (!$user || !$user->reset_code || !$user->reset_code_expires_at || Carbon::parse($user->reset_code_expires_at)->isPast()) {
            return $invalid;
        }

        // Too many wrong codes: throw the code away so it cannot be guessed
        $key = $this->attemptsKey($user->email);
        $attempts = Cache::get($key, 0);
        if ($attempts >= self::MAX_ATTEMPTS) {
            $user->forceFill(['reset_code' => null, 'reset_code_expires_at' => null])->save();
            return $invalid;
        }

        if (!hash_equals($user->reset_code, $this->digest('code', trim($request->code)))) {
            Cache::put($key, $attempts + 1, now()->addMinutes(self::CODE_MINUTES));
            return $invalid;
        }

        // Code is right: replace it with a one-time token for the next step
        $token = Str::random(64);
        $user->reset_code = $this->digest('token', $token);
        $user->reset_code_expires_at = Carbon::now()->addMinutes(self::TOKEN_MINUTES);
        $user->save();
        Cache::forget($key);

        return response()->json([
            'status' => true,
            'message' => 'Code verified. You can now choose a new password.',
            'reset_token' => $token,
        ]);
    }

    // Step 3: Reset password (needs the token from step 2)
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::where('email', $request->email)->first();

        $valid = $user
            && $user->reset_code
            && $user->reset_code_expires_at
            && !Carbon::parse($user->reset_code_expires_at)->isPast()
            && hash_equals($user->reset_code, $this->digest('token', $request->token));

        if (!$valid) {
            return response()->json([
                'status' => false,
                'message' => 'This reset link has expired. Please start again.',
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->reset_code = null;
        $user->reset_code_expires_at = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully.',
        ]);
    }
}
