<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For organisation pages that have no role permission of their own (reports, plans,
 * founders, history, recognition, success stories): only the organisation account
 * itself (or a Super Admin) may use them. Members with a role reach only the modules
 * their role's permissions cover.
 */
class OrgOwnerOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $type = $request->user()?->type;
        if (!in_array($type, ['organisation', 'superadmin'], true)) {
            return response()->json([
                'status' => false,
                'message' => 'Only the organisation account can do this.',
            ], 403);
        }

        return $next($request);
    }
}
