<?php

namespace App\Http\Controllers\SuperAdmin\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use App\Models\User;
use App\Notifications\SupportReplied;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

// Super Admin: read every help request, answer it and change its status
class SupportRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = SupportRequest::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->withCount('messages')
            ->orderByRaw("FIELD(status, 'open', 'answered', 'closed')")
            ->orderByDesc('last_activity_at')
            ->get();

        $orgNames = User::whereIn('id', $requests->pluck('org_id')->filter()->unique())->pluck('org_name', 'id');
        $requests->each(fn ($r) => $r->org_name = $orgNames[$r->org_id] ?? null);

        return response()->json(['status' => true, 'data' => $requests]);
    }

    public function show($id)
    {
        $support = SupportRequest::with('messages')->findOrFail($id);
        $support->org_name = $support->org_id ? User::whereKey($support->org_id)->value('org_name') : null;

        return response()->json(['status' => true, 'data' => $support]);
    }

    public function reply(Request $request, $id)
    {
        $data = $request->validate(['body' => 'required|string|max:5000']);
        $support = SupportRequest::findOrFail($id);

        $support->messages()->create(['user_id' => Auth::id(), 'is_staff' => true, 'body' => trim($data['body'])]);
        $support->update(['status' => 'answered', 'last_activity_at' => now()]);

        // Requests from the public form have no account, so those replies go out by email by hand
        if ($support->user) {
            $support->user->notify(new SupportReplied($support));
        }

        return response()->json(['status' => true, 'data' => $support->load('messages')]);
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['open', 'answered', 'closed'])]]);
        SupportRequest::findOrFail($id)->update(['status' => $data['status']]);

        return response()->json(['status' => true]);
    }
}
