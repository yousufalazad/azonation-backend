<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // Step 1: Send reset code
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        $code = rand(100000, 999999); // 6 digit code

        $user->reset_code = $code;
        $user->reset_code_expires_at = Carbon::now()->addMinutes(10); // code valid for 10 min
        $user->save();

        // Send email
        Mail::to($user->email)->queue(new PasswordResetCodeMail($code));

        // Mail::raw("Your password reset code is: $code", function ($message) use ($user) {
        //     $message->to($user->email)
        //             ->subject('Password Reset Code');
        // });

        return response()->json([
            'status' => true,
            'message' => 'Reset code sent to your email.',
            'data' => $user
        ]);

        // return response()->json(['message' => 'Reset code sent to your email.']);
    }

    // Step 2: Verify reset code
    public function verifyResetCode(Request $request)
    {
        // dd($request->all());exit;
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->reset_code !== $request->code) {
            return response()->json(['message' => 'Invalid code.'], 422);
        }

        if (Carbon::parse($user->reset_code_expires_at)->isPast()) {
            return response()->json(['message' => 'Code expired. Please request again.'], 422);
        }

        // return response()->json(['message' => 'Code verified successfully.']);

        return response()->json([
            'status' => true,
            'message' => 'Code verified successfully.',
            'data' => $user
        ]);
    }

    // Step 3: Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        $user->password = Hash::make($request->password);
        $user->reset_code = null;
        $user->reset_code_expires_at = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully.',
            'data' => $user
        ]);
        // return response()->json(['message' => 'Password reset successfully.']);
    }
}