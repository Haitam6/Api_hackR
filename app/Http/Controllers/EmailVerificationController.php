<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // For making HTTP requests
use Illuminate\Support\Facades\Log as LaravelLog; // For logging
use App\Models\Log; // For logging actions
use App\Models\Droit;
use App\Models\Fonctionnalites;
use App\Models\Role;


class EmailVerificationController extends Controller
{
     /**
     * @OA\Get(
     *     path="/api/emails/verify/{email}",
     *     summary="Verify an email address.",
     *     description="This feature verifies an email address.",
     *     tags={"Fonctionnalités"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="The email address to verify.",
     *         required=true,
     *         @OA\Schema(type="string", format="email", example="example@domain.com")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verification was successful.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="email", type="string", example="example@domain.com"),
     *                 @OA\Property(property="result", type="string", example="deliverable"),
     *                 @OA\Property(property="score", type="integer", example=95),
     *                 @OA\Property(property="smtp_check", type="boolean", example=true),
     *                 @OA\Property(property="regex", type="boolean", example=true),
     *                 @OA\Property(property="gibberish", type="boolean", example=false),
     *                 @OA\Property(property="disposable", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentication failed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="User not authenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Vous n'avez pas le droit pour faire cela.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or email verification failed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Email verification failed")
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

    public function verifyEmail(Request $request, $email)
    {
        $fonctionnalite_id = 4;
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        if (!$this->verifRoles($fonctionnalite_id, $user->role_id)) {
            return response()->json(['error' => 'Vous n\'avez pas le droit pour faire cela.'], 401);
        }

        $url = "https://api.hunter.io/v2/email-verifier?email={$email}&api_key=5a34efd85a1ff9d2493919cc95449ce49d74f0ac";  // Replace with your actual API key

        $response = Http::get($url);

        if ($response->successful()) {
            Log::create([
                'date' => now(),
                'action' => 'email_verification',
                'fonctionnalite_id' => 4, 
                'id_user' => $user->id, 
            ]);

            return response()->json($response->json()); 
        }

        return response()->json(['error' => 'Email verification failed'], 400);
    }
}
