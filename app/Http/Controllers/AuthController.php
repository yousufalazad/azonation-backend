<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Individual;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    // Method to handle successful responses
    protected function success($message, $data = [], $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    // Method to handle error responses
    protected function error($message, $errors = [], $status = 422)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
    // Method to handle individual registration creation
    public function individualRegister(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Create a new user record
        $user = User::create([
            'type' => 1, //type=1 indicating individual type user in user tabel
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create a new individual record associated with the user
        Individual::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            // 'azon_id' => $request->azon_id,
            //'status' => $request->status,
        ]);

        // Return a success response
        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    public function orgProfileUpdate(Request $request, $id)
{
    // Validate the request data
    $request->validate([
        'org_name' => 'required|string|max:255',
        'short_description' => 'required|string|max:1000',
    ]);

    // Find the organisation by ID
    $organisation = Organisation::find($id);

    // Check if the organisation exists
    if (!$organisation) {
        return response()->json([
            'status' => false,
            'message' => 'Organisation not found',
        ], 404);
    }

    // Update the organisation's details
    $organisation->update([
        'org_name' => $request->org_name,
        'short_description' => $request->short_description,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Organisation profile updated successfully',
    ]);
}


    // public function orgProfileUpdate(Request $request, $id)
    // {
    //     // $request->validate([
    //     //     'org_name' => 'string',
    //     //     'short_description' => 'string',
    //     //     'status' => 'string',
    //     // ]);

    //     // Find the organisation by its ID
    //     $organisation = Organisation::find($id);

    //     // Check if the organisation exists
    //     if (!$organisation) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Organisation not found',
    //         ], 404);
    //     }

    //     // Update the organisation with the provided data
    //     $organisation->update([
    //         'org_name' => $request->org_name,
    //         'short_description' => $request->short_description,
    //         // 'status' => $request->status,
    //     ]);

    //     // Return a success response
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Organisation profile updated successfully',
    //     ]);
    // }

    public function orgRegister(Request $request)
    {

        $request->validate([
            'org_name' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
        ]);

        $user = User::create([
            'org_name'  => $request->name,
            'type' => 2, //type= 2 indicating org user in user tabel
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Organisation::create([
            'user_id' => $user->id,
            'org_name' => $request->org_name,
        ]);


        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        // if ($user->id) {
        //     $tokenResult = $user->createToken('Personal Access Token');
        //     $token = $tokenResult->plainTextToken;

        //     return $this->success('Successfully created user!', [
        //         'accessToken' => $token,
        //     ]);
        // } else {
        //     return $this->error('Provide proper details');
        // }


        // // Validate the incoming request data
        // $request->validate([
        //     'email' => 'required|string|email|max:255|unique:users', // Validate email format and uniqueness
        //     'password' => 'required|string|min:8', // Validate password length
        // ]);

        // // Check if type is 2 (indicating organization registration)
        // if ($request->type == 2) {
        //     // Create a new user record
        //     $user = User::create([
        //         'type' => $request->type,
        //         'email' => $request->email,
        //         'password' => Hash::make($request->password),
        //     ]);

        //     // Create a new organization record associated with the user
        //     Organisation::create([
        //         'user_id' => $user->id,
        //         'org_name' => $request->org_name,
        //     ]);

        //     // Return a success response
        //     return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        // }

        // // If type is not 1, return an error response (if needed)
        // return response()->json(['message' => 'Invalid registration type'], 422);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return $this->error('Unauthorized');
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        //$organisation = Organisation::where('user_id',$user->id)->orderBy('id', 'desc')->first();
        //$individual = Individual::where('user_id',$user->id)->orderBy('id', 'desc')->first();

        return $this->success('Successfully logged in', [
            'user_id' => $user->id,
            //'full_name' => $individual->full_name,
            //'org_name' => $organisation->org_name,
            'email' => $user->email,
            'type' => $user->type,
            'accessToken' => $token,
            'token_type' => 'Bearer',
        ]);


        // $request->validate([
        //     'email' => 'required|string|email',
        //     'password' => 'required|string',
        // ]);

        // if (Auth::attempt($request->only('email', 'password'))) {
        //     $user = Auth::user();

        //     // Determine the user type (assuming 'type' is a field in the 'users' table)
        //     $userType = $user->type;

        //     $token = $user->createToken('auth_token')->plainTextToken;

        //     return response()->json([
        //         'token' => $token,
        //         'type' => $userType, // Include the user type in the response
        //     ]);
        // }

        // throw ValidationException::withMessages([
        //     'email' => ['The provided credentials are incorrect.'],
        // ]);
        //}
        // public function login(Request $request)
        // {
        //     $request->validate([
        //         'email' => 'required|string|email',
        //         'password' => 'required|string',
        //     ]);

        //     // if (!Auth::attempt($request->only('email', 'password'))) {
        //     //     throw ValidationException::withMessages([
        //     //         'email' => ['The provided credentials are incorrect.'],
        //     //     ]);
        //     // }

        //     if (Auth::attempt($request->only('email', 'password'))) {
        //         $user = Auth::user();

        //         // Determine the user type (assuming 'type' is a field in the 'users' table)
        //         $userType = $user->type;

        //        $token = $user->createToken('auth_token')->plainTextToken;

        //         return response()->json([
        //             'token' => $token,
        //             'type' => $userType, // Include the user type in the response
        //         ]);
        //     }

        //     throw ValidationException::withMessages([
        //         'email' => ['The provided credentials are incorrect.'],
        //     ]);

        //     // $user = $request->user();

        //     // // Determine the user type (assuming 'type' is a field in the 'users' table)
        //     // $userType = $user->type;

        //     // $token = $user->createToken('auth_token')->plainTextToken;

        //     // return response()->json([
        //     //     'token' => $token,
        //     //     'type' => $userType, // Include the user type in the response
        //     // ]);
    }


    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    // Method to handle logout process
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
