<?php
namespace App\Http\Controllers\Org\Committee;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\OrgMember;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Who serves on a committee, in which role (President, Secretary...) and for how long.
 */
class CommitteeMemberController extends Controller
{
    use ResolvesCurrentOrg;

    public function __construct()
    {
        $this->middleware('org.permission:committee-member.read')->only(['index', 'show']);
        $this->middleware('org.permission:committee-member.create')->only(['create', 'store']);
        $this->middleware('org.permission:committee-member.update')->only(['edit', 'update']);
        $this->middleware('org.permission:committee-member.delete')->only(['destroy']);
    }

    // Members of one committee. An empty committee is an empty list, not an error.
    public function index($id)
    {
        $this->ensureOwnedParent(Committee::class, $id);
        $members = CommitteeMember::query()
            ->where('committee_members.committee_id', $id)
            ->leftJoin('users', 'committee_members.user_id', '=', 'users.id')
            ->leftJoin('designations', 'committee_members.designation_id', '=', 'designations.id')
            ->select('committee_members.*', 'users.first_name', 'users.last_name', 'designations.name as designation_name')
            ->orderBy('committee_members.designation_id')
            ->get();
        return response()->json(['status' => true, 'data' => $members], 200);
    }

    public function create() {}

    public function store(Request $request)
    {
        $this->ensureOwnedParent(Committee::class, $request->input('committee_id'));

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        if ($error = $this->memberError($request->user_id)) {
            return $error;
        }
        $committeeMember = CommitteeMember::create($this->values($request));
        return response()->json(['status' => true, 'data' => $committeeMember, 'message' => 'Committee member added.'], 201);
    }

    public function show($id)
    {
        $committeeMember = $this->ownedVia(CommitteeMember::class, 'committee_id', Committee::class)->find($id);
        if (!$committeeMember) {
            return response()->json(['status' => false, 'message' => 'Committee member not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $committeeMember], 200);
    }

    public function edit($id) {}

    public function update(Request $request, $id)
    {
        if ($request->has('committee_id')) $this->ensureOwnedParent(Committee::class, $request->input('committee_id'));

        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $committeeMember = $this->ownedVia(CommitteeMember::class, 'committee_id', Committee::class)->find($id);
        if (!$committeeMember) {
            return response()->json(['status' => false, 'message' => 'Committee member not found'], 404);
        }
        if ($error = $this->memberError($request->user_id)) {
            return $error;
        }
        $committeeMember->update($this->values($request));
        return response()->json(['status' => true, 'data' => $committeeMember, 'message' => 'Committee member updated.'], 200);
    }

    public function destroy($id)
    {
        $committeeMember = $this->ownedVia(CommitteeMember::class, 'committee_id', Committee::class)->find($id);
        if (!$committeeMember) {
            return response()->json(['status' => false, 'message' => 'Committee member not found'], 404);
        }
        $committeeMember->delete();
        return response()->json(['status' => true, 'message' => 'Committee member removed.'], 200);
    }

    private function rules(): array
    {
        return [
            'committee_id' => 'required|integer|exists:committees,id',
            'user_id' => 'required|integer|exists:users,id',
            'designation_id' => 'required|integer|exists:designations,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }

    private function values(Request $request): array
    {
        return [
            'committee_id' => $request->committee_id,
            'user_id' => $request->user_id,
            'designation_id' => $request->designation_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'note' => $request->note,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];
    }

    // Only people who are members of this organisation can serve on its committees
    private function memberError($userId)
    {
        $isMember = OrgMember::where('org_type_user_id', $this->orgIdOrFail())
            ->where('individual_type_user_id', $userId)
            ->exists();
        return $isMember ? null : response()->json([
            'status' => false,
            'message' => 'This person is not a member of your organisation.',
        ], 422);
    }
}
