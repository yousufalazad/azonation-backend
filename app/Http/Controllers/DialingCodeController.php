<?php

namespace App\Http\Controllers;

use App\Models\DialingCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DialingCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dialingCodes = DialingCode::select('dialing_codes.*', 'countries.country_name as country_name')
            ->leftJoin('countries', 'dialing_codes.country_id', '=', 'countries.id')
            ->get();
        return response()->json(['status' => true, 'data' => $dialingCodes], 200);
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
        // Validation
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'dialing_code' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Logging the inputs for debugging
            Log::info('Dialing Code data: ', ['country_id' => $request->country_id, 'dialing_code' => $request->dialing_code]);

            // Create the Dialing Code record
            $dialingCode = DialingCode::create([
                'country_id' => $request->country_id,
                'dialing_code' => $request->dialing_code,
                'is_active' => $request->is_active,
            ]);

            // Return success response
            return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'Dialing Code created successfully.'], 201);
        } catch (\Exception $e) {
            // Log the error message for troubleshooting
            Log::error('Error creating Country: ' . $e->getMessage());

            // Return error response
            return response()->json(['status' => false, 'message' => 'Failed to create Dialing Code.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DialingCode $dialingCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DialingCode $dialingCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'dialing_code' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        // Find the dialingCode
        $dialingCode = DialingCode::find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'Dialing Code not found.'], 404);
        }

        // Update the dialingCode
        $dialingCode->update([
            'country_id' => $request->country_id,
            'dialing_code' => $request->dialing_code,
            'is_active' => $request->is_active,
        ]);

        return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'Dialing Code updated successfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
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
