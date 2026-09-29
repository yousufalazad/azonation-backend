<?php

namespace App\Http\Controllers\Common;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Help requests to the Azonation team.
 * Signed-in people see and continue only their own requests; the public
 * Contact us form creates a request without an account.
 */
class SupportRequestController extends Controller
{
    use ResolvesCurrentOrg;

    public function index()
    {
        $requests = SupportRequest::where('user_id', Auth::id())
            ->withCount('messages')
            ->orderByRaw("status = 'closed'")
            ->orderByDesc('last_activity_at')
            ->get(['id', 'category', 'subject', 'status', 'last_activity_at', 'created_at']);

        return response()->json(['status' => true, 'data' => $requests]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(SupportRequest::CATEGORIES)],
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:5000',
        ]);
        $user = Auth::user();

        $support = DB::transaction(function () use ($data, $user) {
            $support = SupportRequest::create([
                'user_id' => $user->id,
                'org_id' => $this->currentOrgId(),
                'name' => ($user->type === 'organisation' ? $user->org_name : null)
                    ?: (trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email),
                'email' => $user->email,
                'category' => $data['category'],
                'subject' => trim($data['subject']),
                'status' => 'open',
                'source' => 'app',
                'last_activity_at' => now(),
            ]);
            $support->messages()->create(['user_id' => $user->id, 'is_staff' => false, 'body' => trim($data['message'])]);
            return $support;
        });

        return response()->json(['status' => true, 'data' => ['id' => $support->id]], 201);
    }

    public function show($id)
    {
        $support = SupportRequest::where('user_id', Auth::id())->with('messages')->findOrFail($id);

        return response()->json(['status' => true, 'data' => $this->present($support)]);
    }

    // Add to the conversation; a closed or answered request opens again
    public function reply(Request $request, $id)
    {
        $data = $request->validate(['body' => 'required|string|max:5000']);
        $support = SupportRequest::where('user_id', Auth::id())->findOrFail($id);

        $support->messages()->create(['user_id' => Auth::id(), 'is_staff' => false, 'body' => trim($data['body'])]);
        $support->update(['status' => 'open', 'last_activity_at' => now()]);

        return response()->json(['status' => true, 'data' => $this->present($support->load('messages'))]);
    }

    public function close($id)
    {
        $support = SupportRequest::where('user_id', Auth::id())->findOrFail($id);
        $support->update(['status' => 'closed', 'last_activity_at' => now()]);

        return response()->json(['status' => true]);
    }

    // Public Contact us form (no account needed)
    public function contact(Request $request)
    {
        // Bots fill every field; people never see this one
        if ($request->filled('website')) {
            return response()->json(['status' => true], 201);
        }
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:5000',
        ]);

        DB::transaction(function () use ($data) {
            $support = SupportRequest::create([
                'user_id' => Auth::guard('sanctum')->id(),
                'name' => trim($data['name']),
                'email' => trim($data['email']),
                'category' => 'other',
                'subject' => trim($data['subject']),
                'status' => 'open',
                'source' => 'contact_form',
                'last_activity_at' => now(),
            ]);
            $support->messages()->create(['user_id' => $support->user_id, 'is_staff' => false, 'body' => trim($data['message'])]);
        });

        return response()->json(['status' => true], 201);
    }

    // Staff replies show as "Azonation support", never the staff member's account
    private function present(SupportRequest $support): array
    {
        return [
            'id' => $support->id,
            'category' => $support->category,
            'subject' => $support->subject,
            'status' => $support->status,
            'created_at' => $support->created_at,
            'messages' => $support->messages->map(fn (SupportMessage $m) => [
                'id' => $m->id,
                'is_staff' => $m->is_staff,
                'body' => $m->body,
                'created_at' => $m->created_at,
            ])->values(),
        ];
    }
}
