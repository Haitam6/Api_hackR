<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use App\Models\Droit;
use App\Models\Fonctionnalites;
use App\Models\Role;

class PasswordGeneratorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/passwords/generate",
     *     summary="Generate a Secure Password",
     *     description="Generates a secure password containing at least one uppercase letter, one lowercase letter, one number, and one special character. The default password length is 12 characters, but you can specify a different length.",
     *     operationId="generateSecurePassword",
     *     tags={"Fonctionnalités"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="length",
     *         in="query",
     *         description="The length of the generated password (default is 12 if not provided)",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=12,
     *             example=16
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully generated secure password",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="password", type="string", description="The generated secure password"),
     *             @OA\Property(property="message", type="string", description="Success message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User does not have the right to generate a password",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Error message")
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

    public function generateSecurePassword(Request $request)
    {
        $fonctionnalite_id = 6;
        $length = $request->input('length', 12);

        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_-+=<>?';

        $password = $upper[rand(0, strlen($upper) - 1)] .
                    $lower[rand(0, strlen($lower) - 1)] .
                    $numbers[rand(0, strlen($numbers) - 1)] .
                    $symbols[rand(0, strlen($symbols) - 1)];

        $allCharacters = $upper . $lower . $numbers . $symbols;
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $allCharacters[rand(0, strlen($allCharacters) - 1)];
        }

        $password = str_shuffle($password);

        $user = Auth::user();

        if (!Auth::check()) {
            return response()->json(['error' => 'You are not authentified'], 401);
        }

        if (!$this->verifRoles($fonctionnalite_id, $user->role_id)) {
            return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
        }
        $this->logAction($user ? $user->id : null, 'generate_password', 6);

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
                'fonctionnalite_id' => $actionId,
                'id_user' => $userId,
            ]);
        }
    }
}
