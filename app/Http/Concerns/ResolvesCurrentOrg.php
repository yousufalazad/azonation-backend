<?php

namespace App\Http\Concerns;

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
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
 * Helpers for controllers:
 *   $this->owned(Meeting::class)->findOrFail($id)
 *       only this organisation's meetings (column user_id)
 *   $this->owned(OrgMember::class, 'org_type_user_id')->...
 *       records whose organisation column has another name
 *   $this->ownedVia(MeetingMinutes::class, 'meeting_id', Meeting::class)->findOrFail($id)
 *       child records whose parent belongs to this organisation
 *   $this->ensureOwnedParent(Meeting::class, $request->meeting_id)
 *       refuse (403) when a new child record points at another org's parent
 */
trait ResolvesCurrentOrg
{
    protected function currentOrgId(?Request $request = null): ?int
    {
        $request ??= request();
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if ($user->type === 'organisation') {
            return (int) $user->id;
        }

        // The org access lookup runs several queries: remember it for this request
        if ($request->attributes->has('current_org_id')) {
            return $request->attributes->get('current_org_id');
        }

        $orgId = (int) $request->header('X-Org-Id');
        $resolved = null;
        if ($orgId) {
            $access = collect(app(AuthController::class)->getOrgAccess($user));
            $allowed = $access->contains(fn ($org) => (int) $org['org_type_user_id'] === $orgId);
            $resolved = $allowed ? $orgId : null;
        }

        $request->attributes->set('current_org_id', $resolved);
        return $resolved;
    }

    protected function noOrgResponse()
    {
        return response()->json([
            'status' => false,
            'message' => 'No access to this organisation.',
        ], 403);
    }

    // Current organisation id, or stop the request with 403
    protected function orgIdOrFail(): int
    {
        $orgId = $this->currentOrgId();
        if (!$orgId) {
            throw new HttpResponseException($this->noOrgResponse());
        }
        return $orgId;
    }

    // Query limited to records of the current organisation
    protected function owned(string $model, string $column = 'user_id'): Builder
    {
        $instance = new $model;
        return $model::query()->where($instance->qualifyColumn($column), $this->orgIdOrFail());
    }

    // Query limited to child records whose parent belongs to the current organisation
    protected function ownedVia(string $model, string $foreignKey, string $parent, string $parentColumn = 'user_id'): Builder
    {
        $instance = new $model;
        return $model::query()->whereIn(
            $instance->qualifyColumn($foreignKey),
            $parent::query()->where($parentColumn, $this->orgIdOrFail())->select('id')
        );
    }

    // Stop with 403 unless the parent record belongs to the current organisation
    protected function ensureOwnedParent(string $parent, $parentId, string $parentColumn = 'user_id'): void
    {
        $orgId = $this->orgIdOrFail();
        if (!$parentId || !$parent::query()->where('id', $parentId)->where($parentColumn, $orgId)->exists()) {
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => 'This record does not belong to your organisation.',
            ], 403));
        }
    }
}
