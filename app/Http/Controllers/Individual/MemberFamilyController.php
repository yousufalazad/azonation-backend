<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Models\MemberFamilyPerson;
use App\Models\MemberFamilyShare;
use App\Models\OrgMember;
use App\Services\MemberFamilies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * "My family" in the member area: the member's own family list, and for each of their
 * organisations whether it sees nothing (default), just the numbers, or the details.
 */
class MemberFamilyController extends Controller
{
    private const RELATIONSHIPS = ['spouse', 'child', 'parent', 'sibling', 'grandparent', 'grandchild', 'other'];
    private const MAX_PEOPLE = 50;

    public function index()
    {
        $this->individualOnly();
        $people = MemberFamilyPerson::where('user_id', Auth::id())->orderBy('birth_year')->orderBy('id')->get()
            ->map(fn ($p) => $p->only(['id', 'name', 'relationship', 'birth_year', 'gender', 'note']) + ['age_group' => MemberFamilies::ageGroup($p->birth_year)]);
        $levels = MemberFamilyShare::where('user_id', Auth::id())->pluck('level', 'org_id');

        $orgs = $this->myOrgs()->map(fn ($o) => [
            'org_id' => (int) $o->id,
            'org_name' => $o->org_name,
            'level' => $levels[$o->id] ?? 'none',
        ])->values();

        return response()->json(['status' => true, 'data' => ['people' => $people, 'organisations' => $orgs]]);
    }

    public function store(Request $request)
    {
        $this->individualOnly();
        if (MemberFamilyPerson::where('user_id', Auth::id())->count() >= self::MAX_PEOPLE) {
            return response()->json(['status' => false, 'message' => 'You can add up to ' . self::MAX_PEOPLE . ' people.'], 422);
        }
        $person = MemberFamilyPerson::create($this->validated($request) + ['user_id' => Auth::id()]);
        return response()->json(['status' => true, 'data' => $person], 201);
    }

    public function update(Request $request, $id)
    {
        $this->individualOnly();
        $person = MemberFamilyPerson::where('user_id', Auth::id())->findOrFail($id);
        $person->update($this->validated($request));
        return response()->json(['status' => true, 'data' => $person]);
    }

    public function destroy($id)
    {
        $this->individualOnly();
        MemberFamilyPerson::where('user_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['status' => true]);
    }

    // none = stop sharing; numbers / details = share at that level. Only with the member's own organisations.
    public function share(Request $request, $orgId)
    {
        $this->individualOnly();
        $data = $request->validate(['level' => ['required', Rule::in(['none', 'numbers', 'details'])]]);
        abort_unless($this->myOrgs()->contains('id', (int) $orgId), 404);

        if ($data['level'] === 'none') {
            MemberFamilyShare::where('user_id', Auth::id())->where('org_id', $orgId)->delete();
        } else {
            MemberFamilyShare::updateOrCreate(['user_id' => Auth::id(), 'org_id' => (int) $orgId], ['level' => $data['level']]);
        }
        return response()->json(['status' => true, 'data' => ['org_id' => (int) $orgId, 'level' => $data['level']]]);
    }

    private function validated(Request $request): array
    {
        $year = (int) now()->year;
        return $request->validate([
            'name' => 'required|string|max:100',
            'relationship' => ['required', Rule::in(self::RELATIONSHIPS)],
            'birth_year' => "nullable|integer|min:" . ($year - 120) . "|max:$year",
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'note' => 'nullable|string|max:255',
        ]);
    }

    private function myOrgs()
    {
        return OrgMember::query()
            ->where('org_members.individual_type_user_id', Auth::id())
            ->where('org_members.is_active', '1')
            ->join('users as org', 'org.id', '=', 'org_members.org_type_user_id')
            ->distinct()
            ->orderBy('org.org_name')
            ->get(['org.id', 'org.org_name']);
    }

    private function individualOnly(): void
    {
        abort_unless(Auth::user()?->type === 'individual', 403, 'Only member accounts have a family list.');
    }
}
