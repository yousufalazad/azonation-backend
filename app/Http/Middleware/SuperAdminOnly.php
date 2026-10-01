<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows the request only for platform Super Admin accounts.
 * Use for master data (countries, currencies, packages, prices, roles...)
 * and anything that affects every organisation.
 */
class SuperAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->type !== 'superadmin') {
            return response()->json([
                'status' => false,
                'message' => 'Only Azonation administrators can do this.',
            ], 403);
        }

        return $next($request);
    }
}
