<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class OrgPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // ✅ Get org_id
        $orgId = $request->org_id 
            ?? $request->header('X-Org-Id') 
            ?? $request->route('org_id');

        if (!$orgId) {
            return response()->json([
                'status' => false,
                'message' => 'Organization ID is required'
            ], 400);
        }

        // 🔥 FIX: cast to int (avoid mismatch)
        $orgId = (int) $orgId;

        // 🔥 FIX: safer call
        $authController = app(\App\Http\Controllers\Auth\AuthController::class);
        $orgAccess = collect($authController->getOrgAccess($user));

        // 🔥 DEBUG (optional)
        // \Log::info('OrgAccess', $orgAccess->toArray());

        // ✅ Find org safely
        $org = $orgAccess->first(function ($o) use ($orgId) {
            return (int) $o['org_type_user_id'] === $orgId;
        });

        if (!$org) {
            return response()->json([
                'status' => false,
                'message' => 'No access to this organization'
            ], 403);
        }

        // 🔥 FIX: normalize permission check
        $permissions = collect($org['permissions'] ?? [])
            ->map(fn($p) => strtolower(trim($p)));

        if (!$permissions->contains(strtolower(trim($permission)))) {
            return response()->json([
                'status' => false,
                'message' => 'Permission denied'
            ], 403);
        }

        // ✅ Attach org
        $request->merge([
            'current_org' => $org
        ]);

        return $next($request);
    }
}
