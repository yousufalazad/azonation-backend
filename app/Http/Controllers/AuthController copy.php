<?php

namespace App\Http\Controllers;

use App\Mail\SuperAdminUserRegisteredMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\IndividualUserRegisteredMail;
use App\Mail\OrgUserRegisteredMail;
// use App\Http\Controllers\Validator;
use Illuminate\Support\Facades\Validator;



class AuthController extends Controller
{
    // Method to handle successful responses
    protected function success($message, $data = [], $status = 200)
    {
        return response()->json(data: [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], status: $status);
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
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:3',
            'type' => 'required|string|max:12',
            // 'azon_id' => 'numeric',
        ]);

        // Create a new user record
        // User profile photo/logo path will store in ther user table
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'type' => $request->type,
            'image' => $request->image,
            'password' => Hash::make($request->password),
            // 'azon_id' => $request->azon_id,
        ]);

        // Send email function call
        $this->sendEmail($user);

        // Return a success response
        return response()->json([
            'status' => true,
            'message' => 'Registration successful',
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

        return $this->success(message: 'Successfully logged in', data: [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => $user->type,
            'azon_id' => $user->azon_id,
            'username' => $user->username,

            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,

            'accessToken' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function sendEmail($user)
    {
        if ($user->type == 'individual') {
            Mail::to($user->email)->send(new IndividualUserRegisteredMail($user));
        } elseif ($user->type == 'organisation') {
            Mail::to($user->email)->send(new OrgUserRegisteredMail($user));
        } elseif ($user->type == 'superadmin') {
            Mail::to($user->email)->send(new SuperAdminUserRegisteredMail($user));
        }
    }

    //WHY THIS FUNCTION??????????
    // public function user(Request $request)
    // {
    //     return response()->json($request->user());
    // }


    // No need this function for updating localStorage data
    // public function getUserDataLocalUpdate($userId){

    //     $user = User::find($userId);

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'User all updated data',
    //         'data' => $user
    //     ]);
    // }

    // public function nameUpdate(Request $request, $userId){
    //     $request->validate([
    //         'name' =>'required|string|max:100',
    //     ]);
    //     // $id=Auth::user()->id;
    //     // $id = Auth::id();
    //     $user = User::where('id', $userId)->first();
    //     $user->name = $request->name;
    //     $user->save();

    //     return response()->json([
    //        'status' => true,
    //        'message' => 'Name updated successfully',
    //         'data' => $user
    //     ]);
    // }




    public function nameUpdate(Request $request, $userId)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        try {
            // Find the user or throw a 404 error if not found
            $user = User::findOrFail($userId);

            // Optional: Ensure only authorized users can update their name (add your own authorization logic here)
            // if (auth()->id() !== $user->id) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Unauthorized access',
            //     ], 403); // 403 Forbidden status
            // }

            // Update the user's name
            $user->name = $validated['name'];
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Name updated successfully',
                'data' => $user
            ], 200); // 200 OK status

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404); // 404 Not Found status
        } catch (\Exception $e) {
            // Handle any other exception
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the name',
                'error' => $e->getMessage(),
            ], 500); // 500 Internal Server Error status
        }
    }


    public function usernameUpdate(Request $request, $userId)
    {
        $request->validate([
            'username' => 'required|string|max:30',
        ]);
        // $id=Auth::user()->id;
        // $id = Auth::id();
        $user = User::where('id', $userId)->first();
        $user->username = $request->username;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Username updated successfully',
            'data' => $user
        ]);
    }

    public function userEmailUpdate(Request $request, $userId)
    {
        $request->validate([
            'email' => 'required|string|max:100',
        ]);

        $user = User::where('id', $userId)->first();
        $user->email = $request->email;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Email updated successfully',
            'data' => $user
        ]);
    }


    // Password update
    public function updatePassword(Request $request, $userId)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Find the user by ID
            $user = User::findOrFail($userId); // This will throw an exception if the user is not found

            // Update the user's password
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle user not found
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 422);
        } catch (\Exception $e) {
            // Handle any other exception that occurs during the process
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the password: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Method to handle logout process
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
