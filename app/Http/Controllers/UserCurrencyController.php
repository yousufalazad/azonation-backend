<?php

namespace App\Http\Controllers;

use App\Models\UserCurrency;
use App\Models\User;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserCurrencyController extends Controller
{

    public function getIndividualUsers()
    {

        $individualUsers = User::where('type', 'individual')->get();
        // $individualUsers = User::all();

        return response()->json([
            'status' => true,
            'data' => $individualUsers
        ]);
    }

    // Get all user currencies
    public function index()
    {
        $userCurrencies = UserCurrency::with('user', 'currency')->get();

        return response()->json([
            'status' => true,
            'data' => $userCurrencies,
        ]);
    }

    // Store a new user currency
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id|unique:user_currencies',
            'currency_id' => 'required|exists:currencies,id',
            'status' => 'required|boolean', // Ensure 'status' is passed in the request
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userCurrency = UserCurrency::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'User currency created successfully',
            'data' => $userCurrency,
        ]);
    }

    // Update an existing user currency
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'currency_id' => 'required|exists:currencies,id',
            'status' => 'required|boolean', // Ensure 'status' is passed
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userCurrency = UserCurrency::findOrFail($id);
        $userCurrency->update([
            'currency_id' => $request->currency_id,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User currency updated successfully',
            'data' => $userCurrency,
        ]);
    }

    // Delete a user currency
    public function destroy($id)
    {
        $userCurrency = UserCurrency::findOrFail($id);
        $userCurrency->delete();

        return response()->json([
            'status' => true,
            'message' => 'User currency deleted successfully',
        ]);
    }
}
