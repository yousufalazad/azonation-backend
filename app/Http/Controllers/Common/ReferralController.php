<?php
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\User;
use App\Models\ReferralCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    // Return current logged-in user's referral stats
    public function stats()
    {
        $user = Auth::user();

        return response()->json([
            'referral_code' => $user->referralCode->code ?? null,
            'total_referrals' => $user->referralsMade()->count() ?? 0,
            'completed_referrals' => $user->referralsMade()->where('signup_completed', true)->count() ?? 0,
            'total_rewards' => ReferralReward::where('user_id', $user->id)->sum('amount') ?? 0,
            'successful_referrals' => $user->referralsMade()
                ->with('referredUser')
                ->where('signup_completed', true)
                ->latest()
                ->get()
        ]);
    }

    // Optional: return current user's own referrals
    public function index()
    {
        $user = Auth::user();

        $referrals = $user->referralsMade()
            ->with(['referredUser', 'referralCode'])
            ->latest()
            ->get();

        return response()->json([
            'referrals' => $referrals
        ]);
    }
}
