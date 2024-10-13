<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log as LaravelLog; // Import the correct Log facade

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = new User();
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']); // Hash the password
        $user->save();

        $token = JWTAuth::fromUser($user);

        Log::create([
            'date' => now(),
            'action' => 'register',
            'action_id' => 1,
            'id_user' => $user->id
        ]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        // Manually check credentials
        $user = User::where('email', $credentials['email'])->first();
    
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Password matches, create the token
            $token = JWTAuth::fromUser($user);
    
            // Log successful login
            Log::create([
                'date' => now(),
                'action' => 'login',
                'action_id' => 2, // Unique action ID for login
                'id_user' => $user->id,
            ]);
    
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ]);
        }
    
        // Log failed login attempt
        Log::create([
            'date' => now(),
            'action' => 'login_failed',
            'action_id' => 3, // Unique action ID for failed login
            'id_user' => $user ? $user->id : null, // Log user ID if available
        ]);
    
        return response()->json(['error' => 'Unauthorized'], 401);
    }
   
    public function me(Request $request)
    {
        // Check if the user is authenticated
        $user = auth()->user(); // Get the authenticated user using JWT
    
        if ($user) {
            return response()->json($user); // Return the user's info if authenticated
        } else {
            // Log failed authentication using Laravel's logging system
            LaravelLog::info('Authentication failed for token: ' . $request->bearerToken());
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }    
}
