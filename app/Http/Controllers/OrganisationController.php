<?php

namespace App\Http\Controllers;

use App\Models\OrgAdministrator;
use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\OrgLogo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; // Import Carbon for timestamp


class OrganisationController extends Controller
{
    protected function success($message, $data = [], $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    // getLogo
    public function getLogo($orgId)
    {
        $logo = OrgLogo::where('org_id', $orgId)->orderBy('id', 'desc')->first();
        $imageUrl = $logo ? Storage::url($logo->image) : null;

        return response()->json([
            'status' => true,
            'data' => ['image' => $imageUrl]
        ]);
    }

    // getLogo update 
    public function updateLogo(Request $request, $orgId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20048',
        ]);

        $organisation = Organisation::find($orgId);
        if (!$organisation) {
            return response()->json(['status' => false, 'message' => 'Organization not found'], 404);
        }

        $image = $request->file('image');
        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $timestamp = Carbon::now()->format('YmdHis');
        $newFileName = $timestamp . '_' . $originalName . '.' . $extension;

        $path = $image->storeAs('org/profilelogos', $newFileName, 'public');

        $orgLogo = OrgLogo::where('org_id', $orgId)->orderBy('id', 'desc')->first();
        if ($orgLogo) {
            //ekhane delete korte hobe save korar agee
            $orgLogo->org_id = $orgId;
            $orgLogo->image = $path;
            $orgLogo->save();
        } else {
            // Save the logo path to org_logos table
            $orgLogo = new OrgLogo();
            $orgLogo->org_id = $orgId;
            $orgLogo->image = $path;
            $orgLogo->save();
        }
        $imageUrl = Storage::url($path);
        return response()->json(['status' => true, 'data' => ['image' => $imageUrl]]);
    }

    public function index($id)
    {
        $organisation = Organisation::find($id);
        return response()->json([
            'status' => true,
            'data' => $organisation
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $organisation = Organisation::where('user_id', $id)->first();

        if ($organisation) {
            return response()->json(['status' => true, 'data' => $organisation]);
        } else {
            return response()->json(['status' => false, 'message' => 'Organisation not found']);
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organisation $organisation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $organisation = Organisation::find($id);
        $organisation->update($request->all());
        return response()->json([
            'status' => true,
            'data' => $organisation
        ]);
    }

    
    public function destroy(Organisation $organisation)
    {
        //
    }
}
