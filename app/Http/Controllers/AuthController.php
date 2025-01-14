<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log as LaravelLog;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentification"},
     *     description="Create a new user account by providing a name, email, and password. The password is validated to ensure it's not too common.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User details for registration",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Full name of the user", example="Test Api"),
     *             @OA\Property(property="email", type="string", description="User's email address", example="TestApi@test.com"),
     *             @OA\Property(property="password", type="string", description="Secure password for the user", example="Password123@")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", description="JWT token for authentication", example="jwt.token.here"),
     *             @OA\Property(property="token_type", type="string", description="Token type", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", description="Token validity duration in seconds", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Common password error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The password you provided is too common. Please choose a more secure password.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed for one or more fields."),
     *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $commonPasswords = file(storage_path('common-passwords/10k-most-common.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (in_array($validatedData['password'], $commonPasswords)) {
            return response()->json(['error' => 'The password you provided is too common. Please choose a more secure password.'], 400);
        }

        $user = new User();
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        $user->save();

        $token = JWTAuth::fromUser($user);

        // Log the registration action
        $this->logAction($user->id, 'register', 1);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login",
     *     tags={"Authentification"},
     *     description="Authenticate a user using their email and password, and return a JWT token upon success.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Login credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", description="User's registered email", example="Haitam_elqassimi10@outlook.fr"),
     *             @OA\Property(property="password", type="string", description="User's password", example="haitam")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", description="JWT token for authentication", example="jwt.token.here"),
     *             @OA\Property(property="token_type", type="string", description="Token type", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", description="Token validity duration in seconds", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $token = JWTAuth::fromUser($user);
            $this->logAction($user->id, 'login', 2);
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ]);
        }

        $this->logAction($user ? $user->id : null, 'login_failed', 3);
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * @OA\Get(
     *     path="/api/users/me",
     *     summary="Get current user profile",
     *     tags={"Authentification"},
     *     description="Retrieve the profile information of the currently authenticated user.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", description="User's email address", example="Haitam_elqassimi10@outlook.fr"),
     *             @OA\Property(property="nom", type="string", description="User's full name", example="Haitam Elqassimi"),
     *             @OA\Property(property="statut", type="string", description="User's account status", example="active"),
     *             @OA\Property(property="date_creation", type="string", format="date-time", description="Account creation date", example="2023-01-01T00:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Token absent or invalid")
     *         )
     *     )
     * )
     */
    public function me(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            $this->logAction($user->id, 'consultation profil', 14);

            return response()->json([
                'email' => $user->email,
                'nom' => $user->name,
                'statut' => $user->status,
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

    private function logAction($userId, $action, $actionId)
    {
        Log::create([
            'date' => now(),
            'action' => $action,
            'fonctionnalite_id' => $actionId,
            'id_user' => $userId,
        ]);
    }
}
