<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Individual;
use App\Models\SuperAdmin;

use App\Mail\IndividualUserRegisteredMail;
use App\Mail\OrgUserRegisteredMail;
use Illuminate\Support\Facades\Mail;


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
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:3',
        ]);

        // Create a new user record
        // User profile photo/logo path will store in ther user table
        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'type' => 'individual', // User type => individual
            'image' => $request->image,
            'password' => Hash::make($request->password),
            // 'azon_id' => $request->azon_id,
        ]);

        // Create a new individual record associated with the user
        $individual = Individual::create([
            'user_id' => $user->id,
            // 'azon_id' => $request->azon_id,
            //'status' => $request->status,
        ]);

        // Send the email
        Mail::to($user->email)->send(new IndividualUserRegisteredMail($individual));

        // Return a success response
        //return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
        return response()->json([
            'status' => true,
            'message' => 'Individual registration successful',
            'data' => $user
        ]);
    }

    public function orgRegister(Request $request)
    {

        $request->validate([
           'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:3',
        ]);
 
        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'type' => 'organisation', // User type => organisation
            'image' => $request->image,
            'password' => Hash::make($request->password),
            // 'azon_id' => $request->azon_id,

        ]);

        $org = Organisation::create([
            'user_id' => $user->id,
            //'status' => $request->status,
        ]);

        // Send the email
        Mail::to($user->email)->send(new OrgUserRegisteredMail($org));

        return response()->json([
            'status' => true,
            'message' => 'Organisation registration successful',
            'data' => $user
        ]);
    }

    public function superAdminRegister(Request $request)
    {

        $request->validate([
            'admin_name' => 'required|string',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string',
        ]);

        $user = User::create([
            'admin_name'  => $request->admin_name,
            'type' => 'superadmin', //type= superadmin indicating org user in user tabel
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        SuperAdmin::create([
            'user_id' => $user->id,
            'admin_name' => $request->admin_name,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'SuperAdmin registration successful',
            'data' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return $this->error('Unauthorized user');
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;

        return $this->success('Successfully logged in', [
            'user_id' => $user->id,
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
