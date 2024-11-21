<?php

namespace App\Http\Controllers;

use App\Models\EventSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $eventSummaries = EventSummary::all();
        return response()->json($eventSummaries);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'org_event_id' => 'required|integer',
            'total_member_attendance' => 'nullable|integer',
            'total_guest_attendance' => 'nullable|integer',
            'summary' => 'nullable|string',
            'highlights' => 'nullable|string',
            'feedback' => 'nullable|string',
            'challenges' => 'nullable|string',
            'suggestions' => 'nullable|string',
            'financial_overview' => 'nullable|string',
            'total_expense' => 'nullable|numeric',
            'image_attachment' => 'nullable|file|mimes:jpg,jpeg,png',
            'file_attachment' => 'nullable|file|mimes:pdf,doc,docx',
            'next_steps' => 'nullable|string',
            'privacy_setup_id' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_publish' => 'boolean',
        ]);

        if ($request->hasFile('image_attachment')) {
            $validatedData['image_attachment'] = $request->file('image_attachment')->store('attachments/images');
        }

        if ($request->hasFile('file_attachment')) {
            $validatedData['file_attachment'] = $request->file('file_attachment')->store('attachments/files');
        }

        $validatedData['created_by'] = $request->user();

        $eventSummary = EventSummary::create($validatedData);

        return response()->json(['message' => 'Event summary created successfully!', 'data' => $eventSummary]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $eventSummary = EventSummary::findOrFail($id);
        return response()->json($eventSummary);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $eventSummary = EventSummary::findOrFail($id);

        $validatedData = $request->validate([
            'org_event_id' => 'required|integer',
            'total_member_attendance' => 'nullable|integer',
            'total_guest_attendance' => 'nullable|integer',
            'summary' => 'nullable|string',
            'highlights' => 'nullable|string',
            'feedback' => 'nullable|string',
            'challenges' => 'nullable|string',
            'suggestions' => 'nullable|string',
            'financial_overview' => 'nullable|string',
            'total_expense' => 'nullable|numeric',
            'image_attachment' => 'nullable|file|mimes:jpg,jpeg,png',
            'file_attachment' => 'nullable|file|mimes:pdf,doc,docx',
            'next_steps' => 'nullable|string',
            'privacy_setup_id' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_publish' => 'boolean',
        ]);

        if ($request->hasFile('image_attachment')) {
            $validatedData['image_attachment'] = $request->file('image_attachment')->store('attachments/images');
        }

        if ($request->hasFile('file_attachment')) {
            $validatedData['file_attachment'] = $request->file('file_attachment')->store('attachments/files');
        }

        $validatedData['updated_by'] = Auth::id();

        $eventSummary->update($validatedData);

        return response()->json(['message' => 'Event summary updated successfully!', 'data' => $eventSummary]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $eventSummary = EventSummary::findOrFail($id);

        $eventSummary->delete();

        return response()->json(['message' => 'Event summary deleted successfully!']);
    }
}
