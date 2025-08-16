<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\DialingCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DialingCodeController extends Controller
{
    public function index()
    {
        $dialingCodes = DialingCode::select('dialing_codes.*', 'countries.name as name')
            ->leftJoin('countries', 'dialing_codes.country_id', '=', 'countries.id')
            ->get();

        // $dialingCodes = DialingCode::get();
        return response()->json(['status' => true, 'data' => $dialingCodes], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'dialing_code' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Dialing Code data: ', ['country_id' => $request->country_id, 'dialing_code' => $request->dialing_code]);
            $dialingCode = DialingCode::create([
                'country_id' => $request->country_id,
                'dialing_code' => $request->dialing_code,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'Dialing Code created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Country: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Dialing Code.'], 500);
        }
    }
    public function show(DialingCode $dialingCode) {}
    public function edit(DialingCode $dialingCode) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'dialing_code' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $dialingCode = DialingCode::find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'Dialing Code not found.'], 404);
        }
        $dialingCode->update([
            'country_id' => $request->country_id,
            'dialing_code' => $request->dialing_code,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'Dialing Code updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $dialingCode = DialingCode::find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'Dialing Code not found.'], 404);
        }
        $dialingCode->delete();
        return response()->json(['status' => true, 'message' => 'Dialing Code deleted successfully.'], 200);
    }
}
