<?php

namespace App\Http\Controllers;

use App\Models\OrgLogo;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; 


class OrgLogoController extends Controller
{
    
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

        $path = $image->storeAs('logos', $newFileName, 'public');

        $orgLogo = OrgLogo::where('org_id', $orgId)->orderBy('id', 'desc')->first();
        if ($orgLogo) {
            //ekhane delete korte hobe save korar agee
            $orgLogo->org_id = $orgId;
            $orgLogo->image = $path;
            $orgLogo->save();
        }else{
        // Save the logo path to org_logos table
        $orgLogo = new OrgLogo();
        $orgLogo->org_id = $orgId;
        $orgLogo->image = $path;
        $orgLogo->save();
        }
        $imageUrl = Storage::url($path);
        return response()->json(['status' => true, 'data' => ['image' => $imageUrl]]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    
    public function store(Request $request, $orgId)
    {
        
    }
    /**
     * Display the specified resource.
     */
    public function show(OrgLogo $orgLogo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgLogo $orgLogo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrgLogo $orgLogo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgLogo $orgLogo)
    {
        //
    }
}
