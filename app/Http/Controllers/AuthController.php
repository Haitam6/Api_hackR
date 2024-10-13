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
    // Validate the registration data
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ]);

    // Check if the password is common
    $commonPasswords = file(storage_path('common-passwords/10k-most-common.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($validatedData['password'], $commonPasswords)) {
        return response()->json(['error' => 'The password you provided is too common. Please choose a more secure password.'], 400);
    }

    // Proceed with registration if the password is not common
    $user = new User();
    $user->name = $validatedData['name'];
    $user->email = $validatedData['email'];
    $user->password = Hash::make($validatedData['password']); // Hash the password
    $user->save();

    $token = JWTAuth::fromUser($user);

    // Log the registration action
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
        try {
            // Récupère l'utilisateur à partir du token JWT
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }
    
            // Crée un log pour la consultation du profil
            $log = Log::create([
                'id_user' => $user->id,
                'action_id' => 3, // ID de la consultation de profil
                'action' => 'consultation profil',
                'date' => now(),
            ]);
    
            // Retourne les informations de l'utilisateur
            return response()->json([
                'email' => $user->email,
                'nom' => $user->name,
                'statut' => $user->status, // Si 'status' existe dans la table 'users'
                'date_creation' => $user->created_at,
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expiré'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token invalide'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token absent'], 401);
        }
    }
    

}
