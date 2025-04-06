<?php
namespace App\Http\Controllers\Org\Membership;
use App\Http\Controllers\Controller;

use App\Models\FamilyMember;
use Illuminate\Http\Request;

class FamilyMemberController extends Controller
{
    public function index()
    {
        $familyMembers = FamilyMember::with(['user', 'member'])->latest()->get();
        return response()->json(['status' => true, 'data' => $familyMembers]);

    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // 'member_id' => 'required',
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'day_month_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'relationship' => 'required|in:child,spouse,sibling,dependent,other',
            'life_status' => 'required|in:active,deceased,left',
            // 'is_active' => 'boolean',
        ]);

        $validatedData['user_id'] = $request->user()->id;
        $validatedData['member_id'] = $request->user()->id;

        $familyMember = FamilyMember::create($validatedData);
        return response()->json([
            'status' => true,
            'message' => 'Family member added successfully.',
            'data' => $familyMember
        ], 201);
    }

    public function show(FamilyMember $familyMember)
    {
        return response()->json($familyMember->load(['user', 'member']));
    }
    public function update(Request $request, FamilyMember $familyMember)
    {
        $validatedData = $request->validate([
            // 'member_id' => 'required',
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'day_month_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'relationship' => 'required|in:child,spouse,sibling,dependent,other',
            'life_status' => 'required|in:active,deceased,left',
            // 'is_active' => 'boolean',
        ]);

        $familyMember->update($validatedData);

        return response()->json([
            'message' => 'Family member updated successfully.',
            'data' => $familyMember
        ]);
    }

    public function destroy(FamilyMember $familyMember)
    {
        $familyMember->delete();

        return response()->json(['message' => 'Family member deleted successfully.']);
    }
}
