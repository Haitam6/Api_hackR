<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;

class PasswordGeneratorController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/generate-password",
     *     summary="Generate a secure random password",
     *     tags={"Password Generation"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="length", type="integer", example=12, description="Length of the password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password generated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="password", type="string", example="A1b!cdefghij"),
     *             @OA\Property(property="message", type="string", example="Password generated successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function generateSecurePassword(Request $request)
    {
        // Set default length (if no length is provided)
        $length = $request->input('length', 12);

        // Define possible characters
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_-+=<>?';

        // Ensure password contains at least one character from each category
        $password = $upper[rand(0, strlen($upper) - 1)] .
                    $lower[rand(0, strlen($lower) - 1)] .
                    $numbers[rand(0, strlen($numbers) - 1)] .
                    $symbols[rand(0, strlen($symbols) - 1)];

        // Complete with random characters to reach the desired length
        $allCharacters = $upper . $lower . $numbers . $symbols;
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $allCharacters[rand(0, strlen($allCharacters) - 1)];
        }

        // Shuffle the final password
        $password = str_shuffle($password);

        // Get the authenticated user (if any)
        $user = Auth::user();

        // Log the password generation action
        $this->logAction($user ? $user->id : null, 'generate_password', 6);

        // Return the generated password
        return response()->json([
            'password' => $password,
            'message' => 'Password generated successfully!',
        ]);
    }

    private function logAction($userId, $action, $actionId)
    {
        if ($userId) {
            Log::create([
                'date' => now(),
                'action' => $action,
                'action_id' => $actionId,
                'id_user' => $userId,
            ]);
        }
    }
}
