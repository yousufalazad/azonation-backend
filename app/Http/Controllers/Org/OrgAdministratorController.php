<?php

namespace App\Http\Controllers\Org;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Controllers\Controller;
use App\Models\OrgAdministrator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

/**
 * The organisation's administrator: the named person in charge of the account
 * (shown on invoices). One is current (is_primary = 1); earlier ones are kept
 * as history. Everyone working for the organisation can see who it is; only
 * the organisation account itself can change it.
 */
class OrgAdministratorController extends Controller
{
    use ResolvesCurrentOrg;

    public function index(Request $request)
    {
        $administrators = OrgAdministrator::with(['individualUser:id,azon_id,first_name,last_name,username', 'administratorProfileImage'])
            ->where('org_type_user_id', $this->orgIdOrFail())
            ->orderByDesc('is_primary')
            ->orderByDesc('start_date')
            ->get();

        $administrators->map(function ($admin) {
            $admin->image_url = $admin->administratorProfileImage && $admin->administratorProfileImage->image_path
                ? url(Storage::url($admin->administratorProfileImage->image_path))
                : null;
            unset($admin->administratorProfileImage);
            return $admin;
        });

        return response()->json($administrators);
    }

    public function checkAdministratorExists(Request $request)
    {
        $validated = $request->validate([
            'individual_type_user_id' => 'required|integer|exists:users,id',
        ]);

        $exists = OrgAdministrator::where('org_type_user_id', $this->orgIdOrFail())
            ->where('individual_type_user_id', $validated['individual_type_user_id'])
            ->where('is_primary', 1)
            ->exists();

        return response()->json(['status' => true, 'data' => ['exists' => $exists]]);
    }

    public function getPrimaryAdministrator(Request $request)
    {
        $primaryAdministrator = OrgAdministrator::where('is_active', 1)
            ->where('org_type_user_id', $this->orgIdOrFail())
            ->where('is_primary', 1)
            ->first();

        return response()->json(['status' => true, 'data' => $primaryAdministrator]);
    }

    // Make someone the administrator; the current one moves to history from today
    public function store(Request $request)
    {
        $orgId = $this->ownerOrgOrFail();
        $validated = $request->validate([
            'individual_type_user_id' => 'required|integer|exists:users,id',
            'admin_note' => 'nullable|string|max:255',
        ]);

        $person = User::where('id', $validated['individual_type_user_id'])->where('type', 'individual')->first(['id', 'first_name', 'last_name']);
        if (!$person) {
            return response()->json(['message' => 'Only a person can be the administrator.'], 422);
        }
        $alreadyCurrent = OrgAdministrator::where('org_type_user_id', $orgId)->where('is_primary', 1)
            ->where('individual_type_user_id', $person->id)->exists();
        if ($alreadyCurrent) {
            return response()->json(['message' => 'This person is already the administrator.'], 422);
        }

        DB::transaction(function () use ($orgId, $person, $validated) {
            $today = Carbon::now()->toDateString();
            OrgAdministrator::where('org_type_user_id', $orgId)
                ->where('is_primary', 1)
                ->update(['is_primary' => 0, 'end_date' => $today]);

            OrgAdministrator::create([
                'org_type_user_id' => $orgId,
                'individual_type_user_id' => $person->id,
                'start_date' => $today,
                'first_name' => $person->first_name,
                'last_name' => $person->last_name,
                'admin_note' => $validated['admin_note'] ?? null,
                'is_primary' => 1,
                'is_active' => 1,
            ]);
        });

        return response()->json(['message' => 'Administrator added successfully.'], 201);
    }

    // Correct the dates or note of a record; who it is and whether it is current do not change here
    public function update(Request $request, $id)
    {
        $this->ownerOrgOrFail();
        $admin = $this->owned(OrgAdministrator::class, 'org_type_user_id')->findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'admin_note' => 'nullable|string|max:255',
        ]);

        if ($admin->is_primary && !empty($validated['end_date'])) {
            return response()->json(['message' => 'The current administrator has no end date. Choose a new administrator instead.'], 422);
        }
        $start = $validated['start_date'] ?? $admin->start_date;
        $end = $admin->is_primary ? null : ($validated['end_date'] ?? $admin->end_date);
        if ($end && $start && Carbon::parse($end)->lt(Carbon::parse($start))) {
            return response()->json(['message' => 'End date cannot be before start date.'], 422);
        }

        $admin->update([
            'start_date' => $start,
            'end_date' => $end,
            'admin_note' => array_key_exists('admin_note', $validated) ? $validated['admin_note'] : $admin->admin_note,
        ]);

        return response()->json(['message' => 'Administrator updated successfully.', 'data' => $admin]);
    }

    // Only past administrators can be removed from the history
    public function destroy($id)
    {
        $this->ownerOrgOrFail();
        $admin = $this->owned(OrgAdministrator::class, 'org_type_user_id')->findOrFail($id);
        if ($admin->is_primary) {
            return response()->json(['message' => 'Choose a new administrator before removing the current one.'], 422);
        }
        $admin->delete();

        return response()->json(['message' => 'Administrator deleted successfully.']);
    }

    // Changes are made by the organisation account itself, not by people working for it
    private function ownerOrgOrFail(): int
    {
        $orgId = $this->orgIdOrFail();
        abort_unless(Auth::user()?->type === 'organisation' && (int) Auth::id() === $orgId, 403, 'Only the organisation account can change its administrator.');
        return $orgId;
    }
}
