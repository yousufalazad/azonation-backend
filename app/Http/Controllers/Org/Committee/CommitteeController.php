<?php
namespace App\Http\Controllers\Org\Committee;

use App\Http\Concerns\ResolvesCurrentOrg;
use Illuminate\Routing\Controller;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommitteeController extends Controller
{
    use ResolvesCurrentOrg;

    public function __construct()
    {
        $this->middleware('org.permission:committee.read')->only(['index', 'show']);
        $this->middleware('org.permission:committee.create')->only(['create', 'store']);
        $this->middleware('org.permission:committee.update')->only(['edit', 'update']);
        $this->middleware('org.permission:committee.delete')->only(['destroy']);
    }

    public function index()
    {
        // The current organisation's committees (not the signed-in person's own id,
        // which is wrong for admins acting for an organisation), with how many serve on each
        $committees = $this->owned(Committee::class)
            ->withCount([
                'members',
                'members as active_members_count' => fn ($q) => $q->where('is_active', 1),
            ])
            ->orderBy('id')
            ->get();
        return response()->json(['status' => true, 'data' => $committees]);
    }

    public function create() {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $committee = new Committee($this->values($request));
        $committee->user_id = $this->orgIdOrFail(); // always the current organisation
        $committee->save();
        return response()->json(['status' => true, 'data' => $committee, 'message' => 'Committee created successfully'], 201);
    }

    public function show($committeeId)
    {
        $committee = $this->owned(Committee::class)->withCount('members')->find($committeeId);
        if (!$committee) {
            return response()->json(['status' => false, 'message' => 'Committee not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $committee], 200);
    }

    public function edit($id) {}

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        $committee = $this->owned(Committee::class)->find($id);
        if (!$committee) {
            return response()->json(['status' => false, 'message' => 'Committee not found'], 404);
        }
        $committee->update($this->values($request));
        return response()->json(['status' => true, 'data' => $committee, 'message' => 'Committee updated successfully'], 200);
    }

    public function destroy($id)
    {
        $committee = $this->owned(Committee::class)->find($id);
        if (!$committee) {
            return response()->json(['status' => false, 'message' => 'Committee not found'], 404);
        }
        $committee->members()->delete();
        $committee->delete();
        return response()->json(['status' => true, 'message' => 'Committee deleted successfully.'], 200);
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // Formatted text; the pages clean it before showing it
            'short_description' => 'nullable|string|max:65000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'note' => 'nullable|string|max:5000',
            'is_active' => 'nullable|boolean',
        ];
    }

    private function values(Request $request): array
    {
        return [
            'name' => $request->name,
            'short_description' => $request->short_description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'note' => $request->note,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];
    }
}
