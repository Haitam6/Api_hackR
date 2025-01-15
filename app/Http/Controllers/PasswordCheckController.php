<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use App\Models\Droit;
use App\Models\Fonctionnalites;
use App\Models\Role;

class PasswordCheckController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/passwords/check",
     *     summary="Check password strength",
     *      description="Check if a password is common from a predefined list.",
     *     tags={"Fonctionnalités"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="password123",
     *                 description="The password to check if it is common."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password check result",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The password is common")
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
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. User lacks sufficient permissions.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="You do not have permission to perform this action.")
     *         )
     *     )
     * )
     */


    function verifRoles($fonctionnalite_id, $current_role_id)
    {
        try {
            $droit = Droit::where('fonctionnalite_id', $fonctionnalite_id)
                        ->where('role_id', $current_role_id)
                        ->first();

            if (!$droit) {
                return false; 
            }

            return true; 
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isCommonPassword(Request $request)
    {
        $fonctionnalite_id = 8; 
        $user = Auth::user();
        if (!Auth::check()) {
            return response()->json(['error' => 'You are not authentified'], 401);
        }

        if (!$this->verifRoles($fonctionnalite_id, $user->role_id)) {
            return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
        }

        $validatedData = $request->validate([
            'password' => 'required|string',
        ]);

        $password = $validatedData['password'];
        $filePath = storage_path('common-passwords/10k-most-common.txt');

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Password list file not found'], 500);
        }

        $commonPasswords = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $this->logAction(Auth::id(), 'check_password', 8);

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
                'fonctionnalite_id' => $actionId,
                'id_user' => $userId,
            ]);
        }
    }
}
