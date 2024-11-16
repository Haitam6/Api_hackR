<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;

class PasswordCheckController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/check-password",
     *     summary="Check if a password is common",
     *     tags={"Password Check"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password check result",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"message": "The password is common"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Password list file not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Password list file not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="The password field is required.")
     *         )
     *     )
     * )
     */
    public function isCommonPassword(Request $request)
    {
        // Vérifie si l'utilisateur est authentifié
        if (!Auth::check()) {
            return response()->json(['error' => 'You are not authentified'], 401);
        }

        // Validate the request to ensure the password is provided
        $validatedData = $request->validate([
            'password' => 'required|string',
        ]);

        $password = $validatedData['password'];
        $filePath = storage_path('common-passwords/10k-most-common.txt');

        // Check if file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Password list file not found'], 500);
        }

        // Read file contents into an array
        $commonPasswords = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Log the action of checking password
        $this->logAction(Auth::id(), 'check_password', 8);

        // Check if the password exists in the array
        if (in_array($password, $commonPasswords)) {
            return response()->json(['message' => 'The password is common'], 200);
        } else {
            return response()->json(['message' => 'The password is not common'], 200);
        }
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
