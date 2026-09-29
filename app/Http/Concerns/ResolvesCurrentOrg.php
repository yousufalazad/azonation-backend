<?php

namespace App\Http\Concerns;

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Works out which organisation the current request acts for, so queries can be
 * limited to that organisation's data.
 *
 * - An organisation account acts for itself.
 * - Any other account acts for the org in the X-Org-Id header, but only if it
 *   belongs to that org (checked against its org access list).
 *
 * Returns null when there is no valid organisation; callers should refuse.
 */
trait ResolvesCurrentOrg
{
    protected function currentOrgId(?Request $request = null): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if ($user->type === 'organisation') {
            return (int) $user->id;
        }

        $orgId = (int) ($request ?? request())->header('X-Org-Id');
        if (!$orgId) {
            return null;
        }

        $access = collect(app(AuthController::class)->getOrgAccess($user));
        $allowed = $access->contains(fn ($org) => (int) $org['org_type_user_id'] === $orgId);

        return $allowed ? $orgId : null;
    }

    protected function noOrgResponse()
    {
        return response()->json([
            'status' => false,
            'message' => 'No access to this organisation.',
        ], 403);
    }
}
