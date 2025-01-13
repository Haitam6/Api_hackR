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
     *     path="/api/verify-email/{email}",
     *     summary="Verify email using Hunter.io API",
     *     tags={"Fonctionnalités"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         required=true,
     *         description="Email to verify",
     *         @OA\Schema(type="string", example="example@domain.com")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verification result",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "data": {
     *                     "email": "example@domain.com",
     *                     "result": "deliverable",
     *                     "score": 95
     *                 }
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="User not authenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Email verification failed",
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
