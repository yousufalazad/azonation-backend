<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\LanguageList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LanguageListController extends Controller
{
    public function index()
    {
        $designation = LanguageList::all();
        return response()->json(['status' => true, 'data' => $designation], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_name' => 'required|string|max:255',
            'language_code' => 'required',
            'default' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('LanguageList data: ', ['language_name' => $request->language_name, 'language_code' => $request->language_code, 'default' => $request->default, 'is_active' => $request->is_active]);
            $designation = LanguageList::create([
                'language_name' => $request->language_name,
                'language_code' => $request->language_code,
                'default' => $request->default,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $designation, 'message' => 'LanguageList created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating LanguageList: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create LanguageList.'], 500);
        }
    }
    public function show(LanguageList $languageList) {}
    public function edit(LanguageList $languageList) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'language_name' => 'required|string|max:255',
            'language_code' => 'required',
            'default' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $designation = LanguageList::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'LanguageList not found.'], 404);
        }
        $designation->update([
            'language_name' => $request->language_name,
            'language_code' => $request->language_code,
            'default' => $request->default,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $designation, 'message' => 'LanguageList updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $designation = LanguageList::find($id);
        if (!$designation) {
            return response()->json(['status' => false, 'message' => 'LanguageList not found.'], 404);
        }
        $designation->delete();
        return response()->json(['status' => true, 'message' => 'LanguageList deleted successfully.'], 200);
    }
}
