<?php

namespace App\Http\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * For records that belong to one person (their country, language,
 * notification settings...). Everyone works with their own records;
 * Super Admins may work with anyone's (they manage these from the admin area).
 *
 *   $request->merge(['user_id' => $this->personalOwnerId($request)]);
 *   $this->mine(UserCountry::class)->find($id);
 */
trait OwnsPersonalRecords
{
    protected function isSuperAdmin(?Request $request = null): bool
    {
        return ($request ?? request())->user()?->type === 'superadmin';
    }

    // Whose record this is: the signed-in person, or (Super Admin only) the user_id sent
    protected function personalOwnerId(Request $request): int
    {
        if ($this->isSuperAdmin($request) && $request->filled('user_id')) {
            return (int) $request->input('user_id');
        }
        return (int) $request->user()->id;
    }

    // Query limited to the signed-in person's records (Super Admins: all)
    protected function mine(string $model, string $column = 'user_id'): Builder
    {
        $query = $model::query();
        if (!$this->isSuperAdmin()) {
            $query->where((new $model)->qualifyColumn($column), request()->user()->id);
        }
        return $query;
    }
}
